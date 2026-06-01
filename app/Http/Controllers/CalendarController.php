<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Models\CalendarEvent;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(): View
    {
        return view('calendar.index');
    }

    public function events(Request $request): JsonResponse
    {
        $start = $request->input('start');
        $end   = $request->input('end');
        $user  = auth()->user();
        $events = [];

        // Manual events in the requested range
        CalendarEvent::query()
            ->when($start && $end, fn ($q) => $q
                ->where('start_date', '<', $end)
                ->where(fn ($q2) => $q2
                    ->whereNull('end_date')
                    ->orWhere('end_date', '>=', $start)
                )
            )
            ->get()
            ->each(function (CalendarEvent $e) use (&$events, $user) {
                $canEdit  = $user->isManager() || $e->created_by === $user->id;
                $colorKey = str_replace('bg-', '', $e->color);
                $events[] = [
                    'id'            => 'event-' . $e->id,
                    'title'         => $e->title,
                    'start'         => $e->all_day
                        ? $e->start_date->toDateString()
                        : $e->start_date->toIso8601String(),
                    'end'           => $e->end_date
                        ? ($e->all_day
                            ? $e->end_date->copy()->addDay()->toDateString()
                            : $e->end_date->toIso8601String())
                        : null,
                    'allDay'        => $e->all_day,
                    'className'     => "bg-{$colorKey}-subtle text-{$colorKey}",
                    'extendedProps' => [
                        'type'        => 'manual',
                        'eventId'     => $e->id,
                        'description' => $e->description,
                        'color'       => $e->color,
                        'canEdit'     => $canEdit,
                    ],
                ];
            });

        // Invoice due dates (sent, not paid)
        Invoice::query()
            ->where('status', InvoiceStatus::Sent)
            ->when($start && $end, fn ($q) => $q->whereBetween('due_date', [$start, $end]))
            ->with('client')
            ->get()
            ->each(function (Invoice $inv) use (&$events) {
                $events[] = [
                    'id'            => 'invoice-' . $inv->id,
                    'title'         => 'Due: ' . ($inv->client->name ?? 'Invoice') . ' — ' . format_inr((float) $inv->total),
                    'start'         => $inv->due_date->toDateString(),
                    'allDay'        => true,
                    'className'     => 'bg-warning-subtle text-warning',
                    'extendedProps' => [
                        'type'    => 'invoice',
                        'canEdit' => false,
                        'url'     => route('invoices.show', $inv->id),
                    ],
                ];
            });

        return response()->json($events);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date',
            'all_day'     => 'boolean',
            'color'       => 'required|in:bg-primary,bg-secondary,bg-success,bg-info,bg-danger',
        ]);

        $data['created_by'] = auth()->id();

        $event    = CalendarEvent::create($data);
        $colorKey = str_replace('bg-', '', $event->color);

        return response()->json([
            'success' => true,
            'event'   => [
                'id'            => 'event-' . $event->id,
                'title'         => $event->title,
                'start'         => $event->all_day
                    ? $event->start_date->toDateString()
                    : $event->start_date->toIso8601String(),
                'end'           => $event->end_date
                    ? ($event->all_day
                        ? $event->end_date->copy()->addDay()->toDateString()
                        : $event->end_date->toIso8601String())
                    : null,
                'allDay'        => $event->all_day,
                'className'     => "bg-{$colorKey}-subtle text-{$colorKey}",
                'extendedProps' => [
                    'type'        => 'manual',
                    'eventId'     => $event->id,
                    'description' => $event->description,
                    'color'       => $event->color,
                    'canEdit'     => true,
                ],
            ],
        ]);
    }

    public function update(Request $request, CalendarEvent $event): JsonResponse
    {
        $user = auth()->user();
        if (! $user->isManager() && $event->created_by !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date',
            'all_day'     => 'boolean',
            'color'       => 'required|in:bg-primary,bg-secondary,bg-success,bg-info,bg-danger',
        ]);

        $event->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy(CalendarEvent $event): JsonResponse
    {
        $user = auth()->user();
        if (! $user->isManager() && $event->created_by !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $event->delete();

        return response()->json(['success' => true]);
    }
}
