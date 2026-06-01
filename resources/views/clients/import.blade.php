@extends('layouts.app', ['title' => 'Import Clients', 'subtitle' => 'Clients'])

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-9">

        @if(empty($preview))
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Import Clients from CSV / Excel</h5>
            </div>
            <div class="card-body">

                @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)<p class="mb-0">{{ $error }}</p>@endforeach
                </div>
                @endif

                <p class="text-muted mb-3">
                    Upload a CSV or Excel file with the following columns (in any order):
                    <code>name</code>, <code>email</code>, <code>phone</code>, <code>company</code>,
                    <code>city</code>, <code>state</code>.
                    The first row must be the header row. Clients with duplicate emails will be flagged.
                </p>

                <a id="downloadTemplate" class="btn btn-sm btn-outline-secondary mb-3">
                    <i class="ti ti-download me-1"></i> Download Template
                </a>

                <form action="{{ route('clients.import.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Select File</label>
                        <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls,.txt" required>
                        <div class="form-text">CSV or Excel (.xlsx) — max 5 MB</div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-eye me-1"></i> Preview Import
                    </button>
                    <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                </form>
            </div>
        </div>

        @else
        @php
            $total      = count($preview);
            $errCount   = collect($preview)->filter(fn($r) => $r['error'])->count();
            $dups       = collect($preview)->filter(fn($r) => $r['duplicate'])->count();
            $willImport = $total - $errCount - $dups;
        @endphp
        <div class="alert alert-info mb-3">
            <strong>Preview:</strong> {{ $total }} rows found —
            {{ $willImport }} will be imported,
            {{ $dups }} duplicate(s) will be skipped,
            {{ $errCount }} invalid row(s) skipped.
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Import Preview</h5>
                <a href="{{ route('clients.import.show') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ti ti-upload me-1"></i> Upload Different File
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Company</th>
                                <th>City</th>
                                <th>State</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($preview as $i => $row)
                            <tr class="{{ $row['duplicate'] ? 'table-warning' : ($row['error'] ? 'table-danger' : '') }}">
                                <td class="text-muted small">{{ $i + 1 }}</td>
                                <td>{{ $row['name'] ?: '—' }}</td>
                                <td class="small">{{ $row['email'] ?: '—' }}</td>
                                <td class="small">{{ $row['phone'] ?: '—' }}</td>
                                <td class="small">{{ $row['company'] ?: '—' }}</td>
                                <td class="small">{{ $row['city'] ?: '—' }}</td>
                                <td class="small">{{ $row['state'] ?: '—' }}</td>
                                <td>
                                    @if($row['error'])
                                        <span class="badge bg-danger">{{ $row['error'] }}</span>
                                    @elseif($row['duplicate'])
                                        <span class="badge bg-warning text-dark">Duplicate email</span>
                                    @else
                                        <span class="badge bg-success">OK</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($willImport > 0)
        <form action="{{ route('clients.import.store') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="ti ti-file-import me-1"></i> Confirm Import ({{ $willImport }} clients)
            </button>
            <a href="{{ route('clients.import.show') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
        </form>
        @else
        <div class="alert alert-warning">
            No valid rows to import. <a href="{{ route('clients.import.show') }}">Upload a different file.</a>
        </div>
        @endif
        @endif

    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csv = 'name,email,phone,company,city,state\nJohn Smith,john@example.com,9876543210,Acme Corp,Mumbai,Maharashtra\nJane Doe,jane@example.com,9123456789,Beta Ltd,Delhi,Delhi';
    const link = document.getElementById('downloadTemplate');
    if (link) {
        link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
        link.setAttribute('download', 'clients_template.csv');
    }
});
</script>
@endsection

@endsection
