@extends('layouts.app', ['title' => 'Login History', 'subtitle' => 'Security'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-8">

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Login History</h5>
                <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back to Profile
                </a>
            </div>
            <div class="card-body p-0">
                @if($history->isEmpty())
                    <p class="text-muted p-4 mb-0">No login history found.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date &amp; Time</th>
                                    <th>IP Address</th>
                                    <th>Browser / Device</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history as $entry)
                                    <tr>
                                        <td class="text-nowrap">{{ $entry->created_at->format('d M Y, H:i:s') }}</td>
                                        <td>{{ $entry->ip_address }}</td>
                                        <td class="text-muted small" style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                            {{ $entry->user_agent }}
                                        </td>
                                        <td>
                                            @if($entry->status === 'success')
                                                <span class="badge bg-success">Success</span>
                                            @else
                                                <span class="badge bg-danger">Failed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($history->hasPages())
                        <div class="p-3">
                            {{ $history->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
