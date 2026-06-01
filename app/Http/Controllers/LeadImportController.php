<?php

namespace App\Http\Controllers;

use App\Enums\LeadStage;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadImportController extends Controller
{
    public function show(): View
    {
        return view('leads.import');
    }

    public function preview(Request $request): View
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
        ]);

        $rows = $this->parseFile($request->file('file'));

        if (empty($rows)) {
            return back()->withErrors(['file' => 'The file is empty or could not be parsed.']);
        }

        $existingEmails = Lead::whereNotNull('email')
            ->pluck('email')
            ->map(fn ($e) => strtolower($e))
            ->toArray();

        $preview = [];
        $seenEmails = [];

        foreach ($rows as $row) {
            $email = isset($row['email']) ? strtolower(trim($row['email'])) : '';
            $isDuplicate = $email && (in_array($email, $existingEmails) || in_array($email, $seenEmails));

            if ($email) {
                $seenEmails[] = $email;
            }

            $preview[] = [
                'name'      => trim($row['name'] ?? ''),
                'email'     => trim($row['email'] ?? ''),
                'phone'     => trim($row['phone'] ?? ''),
                'company'   => trim($row['company'] ?? ''),
                'source'    => trim($row['source'] ?? ''),
                'duplicate' => $isDuplicate,
                'error'     => empty(trim($row['name'] ?? '')) ? 'Name is required' : null,
            ];
        }

        session(['lead_import_preview' => $preview]);

        return view('leads.import', compact('preview'));
    }

    public function import(Request $request): RedirectResponse
    {
        $preview = session('lead_import_preview', []);

        if (empty($preview)) {
            return redirect()->route('leads.import.show')
                ->withErrors(['file' => 'No import data found. Please upload a file first.']);
        }

        $inserted = 0;
        $skipped  = 0;

        foreach ($preview as $row) {
            if ($row['duplicate'] || $row['error']) {
                $skipped++;
                continue;
            }

            Lead::create([
                'name'       => $row['name'],
                'email'      => $row['email'] ?: null,
                'phone'      => $row['phone'] ?: null,
                'company'    => $row['company'] ?: null,
                'source'     => $row['source'] ?: null,
                'stage'      => LeadStage::NewLead->value,
                'created_by' => auth()->id(),
            ]);

            $inserted++;
        }

        session()->forget('lead_import_preview');

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['inserted' => $inserted, 'skipped' => $skipped])
            ->log("Imported {$inserted} leads");

        return redirect()->route('leads.index')
            ->with('success', "Imported {$inserted} leads. {$skipped} skipped (duplicates or invalid).");
    }

    private function parseFile(\Illuminate\Http\UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['xlsx', 'xls'])) {
            return $this->parseExcel($file);
        }

        return $this->parseCsv($file);
    }

    private function parseCsv(\Illuminate\Http\UploadedFile $file): array
    {
        $rows    = [];
        $handle  = fopen($file->getRealPath(), 'r');
        $headers = null;

        while (($line = fgetcsv($handle)) !== false) {
            if ($headers === null) {
                $headers = array_map(fn ($h) => strtolower(trim($h)), $line);
                continue;
            }
            if (count($line) === count($headers)) {
                $rows[] = array_combine($headers, $line);
            }
        }

        fclose($handle);
        return $rows;
    }

    private function parseExcel(\Illuminate\Http\UploadedFile $file): array
    {
        if (! class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            return [];
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $sheet       = $spreadsheet->getActiveSheet();
        $data        = $sheet->toArray();

        if (empty($data)) {
            return [];
        }

        $headers = array_map(fn ($h) => strtolower(trim((string) $h)), array_shift($data));
        $rows    = [];

        foreach ($data as $line) {
            if (count($line) === count($headers)) {
                $rows[] = array_combine($headers, array_map('strval', $line));
            }
        }

        return $rows;
    }
}
