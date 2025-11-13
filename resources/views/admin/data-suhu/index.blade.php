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
                {{-- Assuming create will also be a modal, for now, just remove the link --}}
                {{-- <a href="{{ route('data-suhu.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Reading
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
            <table class="table table-bordered table-striped" id="data-suhu-table">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Device</th>
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
                            <td class="reading-section"><span class="badge {{ $reading->section == 'pagi' ? 'bg-info' : 'bg-warning' }}">{{ ucfirst($reading->section) }}</span></td>
                            <td class="reading-temperature">{{ $reading->temperature }} °C</td>
                            <td>
                                <button class="btn btn-sm btn-info edit-reading-btn" data-id="{{ $reading->id }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form action="{{ route('data-suhu.destroy', $reading) }}" method="POST" class="d-inline delete-reading-form">
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
                                <option value="siang">Siang</option>
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
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Pass devices data to JavaScript for dropdown population
            const allDevices = @json($devices);

            // Handle Edit button click
            $('#data-suhu-table').on('click', '.edit-reading-btn', function() {
                const readingId = $(this).data('id');
                const url = "{{ route('data-suhu.edit', ':id') }}".replace(':id', readingId);

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
                const url = "{{ route('data-suhu.update', ':id') }}".replace(':id', readingId);
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

            // Handle delete form submission via AJAX with SweetAlert2
            $('#data-suhu-table').on('submit', '.delete-reading-form', function(e) {
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
                                    'Error deleting data suhu.',
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