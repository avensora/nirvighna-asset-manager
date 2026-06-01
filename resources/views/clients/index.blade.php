@extends('layouts.app', ['title' => 'Clients', 'subtitle' => 'Business'])

@section('content')

<div class="row mb-3">
    <div class="col">
        <a href="{{ route('clients.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Client
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($clients->isEmpty())
            <p class="text-muted text-center py-4">No clients yet. <a href="{{ route('clients.create') }}">Add your first client.</a></p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>Added</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                        <tr>
                            <td>
                                <a href="{{ route('clients.show', $client) }}" class="fw-semibold text-body">
                                    {{ $client->name }}
                                </a>
                            </td>
                            <td>{{ $client->company ?? '—' }}</td>
                            <td>
                                @if($client->email)
                                    <a href="mailto:{{ $client->email }}">{{ $client->email }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $client->phone ?? '—' }}</td>
                            <td>{{ $client->city ?? '—' }}</td>
                            <td>{{ $client->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="ti ti-eye"></i>
                                </a>
                                <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="ti ti-pencil"></i>
                                </a>
                                <form action="{{ route('clients.destroy', $client) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete {{ addslashes($client->name) }}? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($clients->hasPages())
                <div class="mt-3">
                    {{ $clients->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

@endsection
