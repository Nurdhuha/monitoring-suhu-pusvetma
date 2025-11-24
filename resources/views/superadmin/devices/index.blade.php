@extends('adminlte::page')

@section('title', 'Manage Devices')

@section('content_header')
    <h1>Manage Devices</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Device List</h3>
            <div class="card-tools">
                <a href="{{ route('superadmin.devices.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Device
                </a>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-check"></i> Success!</h5>
                    {{ session('success') }}
                </div>
            @endif
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Added By</th>
                        <th>Created At</th>
                        <th style="width: 150px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($devices as $device)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $device->name }}</td>
                            <td>{{ $device->location ?? 'N/A' }}</td>
                            <td>{{ $device->user->name ?? 'N/A' }}</td>
                            <td>{{ $device->created_at->format('d M Y H:i') }}</td>
                            <td>
                                {{-- <a href="{{ route('superadmin.devices.edit', $device->id) }}" class="btn btn-info btn-sm">Edit</a> --}}
                                <form action="{{ route('superadmin.devices.destroy', $device->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm delete-btn">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No devices found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $devices->links() }}
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            const form = this;
            const deviceName = $(this).closest('tr').find('td:nth-child(2)').text();

            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete the device: ${deviceName}. You won't be able to revert this!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@stop
