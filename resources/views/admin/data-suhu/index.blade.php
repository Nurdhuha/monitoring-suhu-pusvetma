@extends('adminlte::page')

@section('title', 'Manage Data Suhu')

@section('content_header')
    <h1>Manage Data Suhu</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Suhu List</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-success btn-sm mr-2" data-toggle="modal" data-target="#downloadExcelModal">
                    <i class="fas fa-download"></i> Download Excel
                </button>
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createDataSuhuModal">
                    <i class="fas fa-plus"></i> Input Suhu
                </button>
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
            <table class="table table-bordered table-striped" id="data-suhu-table">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Device</th>
                        <th>Admin</th>
                        <th>Section</th>
                        <th>Temperature</th>
                        <th>Recorded At</th>
                        <th style="width: 150px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataSuhu as $reading)
                        <tr data-id="{{ $reading->id }}">
                            <td>{{ $loop->iteration }}</td>
                            <td class="reading-device-name">{{ $reading->device->name ?? 'N/A' }}</td>
                            <td>{{ $reading->user->name ?? 'N/A' }}</td>
                            <td class="reading-section"><span class="badge {{ $reading->section == 'pagi' ? 'bg-info' : 'bg-warning' }}">{{ ucfirst($reading->section) }}</span></td>
                            <td class="reading-temperature">{{ $reading->temperature }} °C</td>
                            <td>{{ $reading->created_at->format('d M Y H:i') }}</td> {{-- Recorded At content --}}
                            <td> {{-- Action buttons --}}
                                <button class="btn btn-sm btn-info edit-reading-btn" data-id="{{ $reading->id }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form action="{{ route('admin.data-suhu.destroy', $reading) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger delete-confirm-btn">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No temperature readings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $dataSuhu->links() }}
        </div>
        </div>
    </div>

    {{-- Edit Data Suhu Modal --}}
    <div class="modal fade" id="editDataSuhuModal" tabindex="-1" role="dialog" aria-labelledby="editDataSuhuModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDataSuhuModalLabel">Edit Data Suhu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editDataSuhuForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" id="reading_id" name="reading_id">
                        <div class="form-group">
                            <label for="edit_device_id">Device</label>
                            <select class="form-control" id="edit_device_id" name="device_id" required>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->location }})</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback" role="alert" id="device_id-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="edit_section">Section</label>
                            <select class="form-control" id="edit_section" name="section" required>
                                <option value="pagi">Pagi</option>
                                <option value="sore">Sore</option>
                            </select>
                            <span class="invalid-feedback" role="alert" id="section-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="edit_temperature">Temperature (°C)</label>
                            <input type="number" step="0.01" class="form-control" id="edit_temperature" name="temperature" required>
                            <span class="invalid-feedback" role="alert" id="temperature-error"></span>
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

    {{-- Create Data Suhu Modal --}}
    <div class="modal fade" id="createDataSuhuModal" tabindex="-1" role="dialog" aria-labelledby="createDataSuhuModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createDataSuhuModalLabel">Input Data Suhu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="createDataSuhuForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="create_device_id">Device</label>
                            <select class="form-control" id="create_device_id" name="device_id" required>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->location }})</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback" role="alert" id="create-device_id-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="create_section">Section</label>
                            <select class="form-control" id="create_section" name="section" required>
                                <option value="pagi">Pagi</option>
                                <option value="sore">Sore</option>
                            </select>
                            <span class="invalid-feedback" role="alert" id="create-section-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="create_temperature">Temperature (°C)</label>
                            <input type="number" step="0.01" class="form-control" id="create_temperature" name="temperature" required>
                            <span class="invalid-feedback" role="alert" id="create-temperature-error"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('admin.data-suhu.download-modal')
@endsection

@section('js')

    <script>

        $(document).ready(function() {
            console.log('Admin data-suhu script loaded.');

            // Pass devices data to JavaScript for dropdown population
            const allDevices = @json($devices);

            // Handle Edit button click
            $('#data-suhu-table').on('click', '.edit-reading-btn', function() {
                const readingId = $(this).data('id');
                const url = "{{ route('admin.data-suhu.edit', ':id') }}".replace(':id', readingId);

                // Clear previous errors
                $('#editDataSuhuForm .invalid-feedback').text('').hide();
                $('#editDataSuhuForm .form-control').removeClass('is-invalid');

                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(response) {
                        $('#reading_id').val(response.id);
                        $('#edit_device_id').val(response.device_id);
                        $('#edit_section').val(response.section);
                        $('#edit_temperature').val(response.temperature);
                        $('#editDataSuhuModal').modal('show');
                    },
                    error: function(xhr) {
                        alert('Error fetching data suhu.');
                        console.error(xhr);
                    }
                });
            });

            // Handle form submission via AJAX
            $('#editDataSuhuForm').on('submit', function(e) {
                e.preventDefault();

                const readingId = $('#reading_id').val();
                const url = "{{ route('admin.data-suhu.update', ':id') }}".replace(':id', readingId);
                const formData = $(this).serialize();

                // Clear previous errors
                $('#editDataSuhuForm .invalid-feedback').text('').hide();
                $('#editDataSuhuForm .form-control').removeClass('is-invalid');

                $.ajax({
                    url: url,
                    method: 'POST', // Use POST for PUT/PATCH with _method field
                    data: formData,
                    success: function(response) {
                        $('#editDataSuhuModal').modal('hide');
                        Swal.fire(
                            'Success!',
                            response.success,
                            'success'
                        );
                        // Update table row
                        const row = $(`tr[data-id="${readingId}"]`);
                        const deviceName = allDevices.find(d => d.id == $('#edit_device_id').val()).name;
                        const sectionBadgeClass = $('#edit_section').val() === 'pagi' ? 'bg-info' : 'bg-warning';
                        const sectionText = $('#edit_section').val().charAt(0).toUpperCase() + $('#edit_section').val().slice(1);

                        row.find('.reading-device-name').text(deviceName);
                        row.find('.reading-section').html(`<span class="badge ${sectionBadgeClass}">${sectionText}</span>`);
                        row.find('.reading-temperature').text($('#edit_temperature').val() + ' °C');
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
                                'Error updating data suhu.',
                                'error'
                            );
                            console.error(xhr);
                        }
                    }
                });
            });

            // Handle create form submission via AJAX
            $('#createDataSuhuForm').on('submit', function(e) {
                e.preventDefault();

                const url = "{{ route('admin.data-suhu.store') }}";
                const formData = $(this).serialize();

                // Clear previous errors
                $('#createDataSuhuForm .invalid-feedback').text('').hide();
                $('#createDataSuhuForm .form-control').removeClass('is-invalid');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#createDataSuhuModal').modal('hide');
                        Swal.fire(
                            'Success!',
                            response.success,
                            'success'
                        );
                        // Reload page to see new data with pagination
                        location.reload();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) { // Validation errors
                            const errors = xhr.responseJSON.errors;
                            for (const field in errors) {
                                const input = $(`#create_${field}`);
                                input.addClass('is-invalid');
                                $(`#create-${field}-error`).text(errors[field][0]).show();
                            }
                        } else {
                            Swal.fire(
                                'Error!',
                                'Error creating data suhu.',
                                'error'
                            );
                            console.error(xhr);
                        }
                    }
                });
            });

            // Handle download excel form submission
            $('#downloadExcelForm').on('submit', function(e) {
                e.preventDefault();

                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                
                // Determine the correct route based on the user's role
                const downloadRoute = "{{ Auth::user()->isSuperAdmin() ? route('superadmin.data-suhu.download') : route('admin.data-suhu.download') }}";

                // Construct the URL with query parameters
                const url = new URL(downloadRoute);
                if (startDate) {
                    url.searchParams.append('start_date', startDate);
                }
                if (endDate) {
                    url.searchParams.append('end_date', endDate);
                }

                // Trigger the download
                window.location.href = url.toString();

                // Close the modal
                $('#downloadExcelModal').modal('hide');
            });

            // Handle delete button click with SweetAlert
            $('#data-suhu-table').on('click', '.delete-confirm-btn', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                const readingId = form.find('input[name="_method"]').prev().val(); // Get the reading ID from a hidden input if available, or data-id from the row
                const deviceName = $(this).closest('tr').find('.reading-device-name').text();
                const section = $(this).closest('tr').find('.reading-section .badge').text();
                const temperature = $(this).closest('tr').find('.reading-temperature').text();


                Swal.fire({
                    title: 'Are you sure?',
                    html: `You are about to delete the temperature reading for:<br>
                           <strong>Device:</strong> ${deviceName}<br>
                           <strong>Section:</strong> ${section}<br>
                           <strong>Temperature:</strong> ${temperature}`,
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

@endsection