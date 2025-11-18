@extends('adminlte::page')

@section('title', 'Manage Devices')

@section('content_header')
    <h1>Manage Devices</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Device List</h3>
            <div class="card-tools">
                {{-- Assuming create will also be a modal, for now, just remove the link --}}
                {{-- <a href="{{ route('devices.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Device
                </a> --}}
            </div>
        </div>
        <div class="card-body">
            <div id="alert-container">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-check"></i> Success!</h5>
                        {{ session('success') }}
                    </div>
                @endif
            </div>
            <table class="table table-bordered table-striped" id="devices-table">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Responsible Admin</th>
                        <th>Created At</th>
                        <th style="width: 150px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($devices as $device)
                        <tr data-id="{{ $device->id }}">
                            <td>{{ $loop->iteration }}</td>
                            <td class="device-name">{{ $device->name }}</td>
                            <td class="device-location">{{ $device->location }}</td>
                            <td>{{ $device->user->name }}</td>
                            <td>{{ $device->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <button class="btn btn-sm btn-info edit-device-btn" data-id="{{ $device->id }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form action="{{ route('superadmin.devices.destroy', $device) }}" method="POST" class="d-inline delete-device-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
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

    {{-- Edit Device Modal --}}
    <div class="modal fade" id="editDeviceModal" tabindex="-1" role="dialog" aria-labelledby="editDeviceModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDeviceModalLabel">Edit Device</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editDeviceForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" id="device_id" name="device_id">
                        <div class="form-group">
                            <label for="edit_name">Device Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                            <span class="invalid-feedback" role="alert" id="name-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="edit_location">Location</label>
                            <input type="text" class="form-control" id="edit_location" name="location">
                            <span class="invalid-feedback" role="alert" id="location-error"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Handle Edit button click
            $('#devices-table').on('click', '.edit-device-btn', function() {
                const deviceId = $(this).data('id');
                const url = "{{ route('superadmin.devices.edit', ':id') }}".replace(':id', deviceId);

                // Clear previous errors
                $('#editDeviceForm .invalid-feedback').text('').hide();
                $('#editDeviceForm .form-control').removeClass('is-invalid');

                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(response) {
                        $('#device_id').val(response.id);
                        $('#edit_name').val(response.name);
                        $('#edit_location').val(response.location);
                        $('#editDeviceModal').modal('show');
                    },
                    error: function(xhr) {
                        alert('Error fetching device data.');
                        console.error(xhr);
                    }
                });
            });

            // Handle form submission via AJAX
            $('#editDeviceForm').on('submit', function(e) {
                e.preventDefault();

                const deviceId = $('#device_id').val();
                const url = "{{ route('superadmin.devices.update', ':id') }}".replace(':id', deviceId);
                const formData = $(this).serialize();

                // Clear previous errors
                $('#editDeviceForm .invalid-feedback').text('').hide();
                $('#editDeviceForm .form-control').removeClass('is-invalid');

                $.ajax({
                    url: url,
                    method: 'POST', // Use POST for PUT/PATCH with _method field
                    data: formData,
                    success: function(response) {
                        $('#editDeviceModal').modal('hide');
                        Swal.fire(
                            'Success!',
                            response.success,
                            'success'
                        );
                        // Update table row
                        const row = $(`tr[data-id="${deviceId}"]`);
                        row.find('.device-name').text($('#edit_name').val());
                        row.find('.device-location').text($('#edit_location').val());
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) { // Validation errors
                            const errors = xhr.responseJSON.errors;
                            for (const field in errors) {
                                const input = $(`#edit_${field}`);
                                input.addClass('is-invalid');
                                $(`#${field}-error`).text(errors[field][0]).show();
                            }
                        } else {
                            Swal.fire(
                                'Error!',
                                'Error updating device.',
                                'error'
                            );
                            console.error(xhr);
                        }
                    }
                });
            });

            // Handle delete form submission via AJAX with SweetAlert2
            $('#devices-table').on('submit', '.delete-device-form', function(e) {
                e.preventDefault();
                const form = $(this);
                const url = form.attr('action');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            method: 'POST', // Use POST for DELETE with _method field
                            data: form.serialize(),
                            success: function(response) {
                                Swal.fire(
                                    'Deleted!',
                                    response.success,
                                    'success'
                                );
                                form.closest('tr').remove(); // Remove the row from the table
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Error!',
                                    'Error deleting device.',
                                    'error'
                                );
                                console.error(xhr);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection