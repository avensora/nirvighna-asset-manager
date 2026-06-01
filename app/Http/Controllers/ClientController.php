<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = Client::with('creator')->latest()->paginate(20);
        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'gstin'   => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'city'    => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:100',
            'pincode' => 'nullable|digits:6',
            'notes'   => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();
        $client = Client::create($data);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($client)
            ->withProperties(['name' => $client->name])
            ->log('Created client');

        return redirect()->route('clients.show', $client)
            ->with('success', "Client \"{$client->name}\" added successfully.");
    }

    public function show(Client $client): View
    {
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client): View
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'gstin'   => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'city'    => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:100',
            'pincode' => 'nullable|digits:6',
            'notes'   => 'nullable|string',
        ]);

        $client->update($data);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($client)
            ->withProperties(['name' => $client->name])
            ->log('Updated client');

        return redirect()->route('clients.show', $client)
            ->with('success', "Client \"{$client->name}\" updated successfully.");
    }

    public function destroy(Client $client): RedirectResponse
    {
        $name = $client->name;

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['name' => $name])
            ->log('Deleted client');

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', "Client \"{$name}\" deleted.");
    }
}
