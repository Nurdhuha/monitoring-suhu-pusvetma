@extends('adminlte::page')

@section('title', 'Manage Data Suhu')

@section('content_header')
    <h1>Manage Data Suhu</h1>
@endsection

@section('css')
    <style>
        .chart-container {
            position: relative;
            height: 40vh;
            width: 100%;
        }
        .select2-container {
            width: 100% !important; /* Make Select2 responsive */
        }
=======
        /* Fix Select2 z-index in modals */
        .select2-container--open {
            z-index: 9999999 !important;
        }
        #chartLoadingIndicator {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 10;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
>>>>>>> master
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Temperature Chart</h3>
        </div>
        <div class="card-body">
            <form id="chartFilterForm" action="{{ route('admin.data-suhu.index') }}" method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="chart_device_ids">Filter by Device (Chart):</label>
                            <select name="chart_device_ids[]" id="chart_device_ids" class="form-control select2" multiple="multiple">
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}" {{ in_array($device->id, $selectedChartDeviceIds) ? 'selected' : '' }}>
                                        {{ $device->name }} ({{ $device->location }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="time_range">Time Range:</label>
                            <select name="time_range" id="time_range" class="form-control" onchange="this.form.submit()">
                                <option value="5_days" {{ $timeRange == '5_days' ? 'selected' : '' }}>5 Hari</option>
                                <option value="1_month" {{ $timeRange == '1_month' ? 'selected' : '' }}>1 Bulan</option>
                                <option value="6_months" {{ $timeRange == '6_months' ? 'selected' : '' }}>6 Bulan</option>
                                <option value="1_year" {{ $timeRange == '1_year' ? 'selected' : '' }}>1 Tahun</option>
                                <option value="all_time" {{ $timeRange == 'all_time' ? 'selected' : '' }}>Semua</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
            <div class="chart-container">
                <canvas id="suhuChart"></canvas>
            </div>
        </div>
    </div>

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
            <form action="{{ route('admin.data-suhu.index') }}" method="GET" class="form-inline mb-3">
                <div class="form-group">
                    <label for="list_device_id" class="mr-2">Filter by Device (List):</label>
                    <select name="list_device_id" id="list_device_id" class="form-control" onchange="this.form.submit()">
                        <option value="">All Devices</option>
                        @foreach($devices as $device)
                            <option value="{{ $device->id }}" {{ $selectedListDeviceId == $device->id ? 'selected' : '' }}>
                                {{ $device->name }} ({{ $device->location }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

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
                            <td>{{ $reading->created_at->format('d M Y H:i') }}</td>
                            <td>
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
                            <td colspan="7" class="text-center">No temperature readings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $dataSuhu->appends(request()->except('page'))->links() }}
        </div>
    </div>

    {{-- Download Excel Modal --}}
    <div class="modal fade" id="downloadExcelModal" tabindex="-1" role="dialog" aria-labelledby="downloadExcelModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="downloadExcelModalLabel">Download Data Suhu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="downloadExcelForm" action="{{ route('admin.data-suhu.download') }}" method="GET">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="download_device_id">Device</label>
                            <select name="device_id" id="download_device_id" class="form-control">
                                <option value="">All Devices</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">
                                        {{ $device->name }} ({{ $device->location }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Download</button>
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
                    <h5 class="modal-title" id="createDataSuhuModalLabel">Input Suhu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="createDataSuhuForm" action="{{ route('admin.data-suhu.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="create_device_id">Device</label>
                            <select class="form-control" id="create_device_id" name="device_id" required>
                                <option value="">-- Select a Device --</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->location }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="create_section">Section</label>
                            <select class="form-control" id="create_section" name="section" required>
                                <option value="">-- Select a Section --</option>
                                <option value="pagi">Pagi</option>
                                <option value="sore">Sore</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="create_temperature">Temperature (°C)</label>
                            <input type="number" step="0.1" class="form-control" id="create_temperature" name="temperature" placeholder="Enter temperature" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Data Suhu Modal --}}
    <div class="modal fade" id="editDataSuhuModal" tabindex="-1" role="dialog" aria-labelledby="editDataSuhuModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDataSuhuModalLabel">Edit Suhu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editDataSuhuForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_device_id">Device</label>
                            <select class="form-control" id="edit_device_id" name="device_id" required>
                                <option value="">-- Select a Device --</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->location }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_section">Section</label>
                            <select class="form-control" id="edit_section" name="section" required>
                                <option value="">-- Select a Section --</option>
                                <option value="pagi">Pagi</option>
                                <option value="sore">Sore</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_temperature">Temperature (°C)</label>
                            <input type="number" step="0.1" class="form-control" id="edit_temperature" name="temperature" placeholder="Enter temperature" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Initialize Select2 for Chart Filter
            $('#chart_device_ids').select2({
                placeholder: "Select devices",
                allowClear: true
            });

            // Initialize Select2 for Modals
            $('#download_device_id, #create_device_id, #edit_device_id').select2({
                placeholder: "Select a device",
                allowClear: true,
                dropdownParent: function(element) {
                    // Attach dropdown to the modal instead of the body
                    return $(element).closest('.modal');
                }
            });

            // Chart Filter Form Submission
            $('#chart_device_ids').on('select2:close', function (e) {
                $(this).closest('form').submit();
            });

            // Chart.js Initialization
            function getTimeUnit(timeRange) {
                switch (timeRange) {
                    case '5_days': return 'day';
                    case '1_month': return 'day';
                    case '6_months': return 'month';
                    case '1_year': return 'month';
                    default: return 'month';
                }
            }

            const ctx = document.getElementById('suhuChart');
            if (ctx) {
                const timeRange = @json($timeRange);
                const timeUnit = getTimeUnit(timeRange);
                const chartData = @json($chartData);

                chartData.datasets.forEach(dataset => {
                    dataset.pointBackgroundColor = function(context) {
                        var index = context.dataIndex;
                        var data = context.dataset.data[index];
                        return data.section === 'pagi' ? 'rgb(54, 162, 235)' : 'rgb(255, 206, 86)';
                    };
                    dataset.borderWidth = 1;
                    dataset.pointRadius = 2;
                    dataset.pointBorderWidth = 0;
                    dataset.pointHoverRadius = 2;
                    dataset.pointHoverBorderWidth = 0;
                    dataset.pointBorderColor = 'rgba(0, 0, 0, 0)';
                    dataset.pointHoverBorderColor = 'rgba(0, 0, 0, 0)';
                });

                new Chart(ctx.getContext('2d'), {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxes: [{
                                type: 'time',
                                time: {
                                    unit: timeUnit,
                                    tooltipFormat: 'll HH:mm'
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Date'
                                }
                            }],
                            yAxes: [{
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Temperature (°C)'
                                }
                            }]
                        },
                        plugins: {
                            tooltip: { mode: 'index', intersect: false },
                            legend: {
                                display: chartData.datasets.length > 1
                            }
                        },
                        hover: { mode: 'nearest', intersect: true }
                    }
                });
            }

            // Create Data Suhu Form Submission
            $('#createDataSuhuForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                $.ajax({
                    type: 'POST',
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function(response) {
                        $('#createDataSuhuModal').modal('hide');
                        $('#alert-container').html(
                            `<div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h5><i class="icon fas fa-check"></i> Success!</h5>
                                ${response.success}
                            </div>`
                        );
                        // Optionally, reload or prepend data to the table
                        location.reload();
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON.errors;
                        let errorHtml = '<div class="alert alert-danger"><ul>';
                        $.each(errors, function(key, value) {
                            errorHtml += '<li>' + value[0] + '</li>';
                        });
                        errorHtml += '</ul></div>';
                        form.find('.modal-body').prepend(errorHtml);
                    }
                });
            });

            // Edit Reading Button Click Handler
            $('.edit-reading-btn').on('click', function() {
                const readingId = $(this).data('id');
                const editUrl = '{{ route("admin.data-suhu.edit", ":id") }}'.replace(':id', readingId);

                $.get(editUrl, function(data) {
                    const updateUrl = '{{ route("admin.data-suhu.update", ":id") }}'.replace(':id', data.id);
                    $('#editDataSuhuForm').attr('action', updateUrl);
                    $('#edit_device_id').val(data.device_id).trigger('change');
                    $('#edit_section').val(data.section);
                    $('#edit_temperature').val(data.temperature);
                    $('#editDataSuhuModal').modal('show');
                });
            });

            // Edit Data Suhu Form Submission
            $('#editDataSuhuForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                $.ajax({
                    type: 'POST', // Should be POST with _method=PUT
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function(response) {
                        $('#editDataSuhuModal').modal('hide');
                        $('#alert-container').html(
                            `<div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h5><i class="icon fas fa-check"></i> Success!</h5>
                                ${response.success}
                            </div>`
                        );
                        location.reload();
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON.errors;
                        let errorHtml = '<div class="alert alert-danger"><ul>';
                        $.each(errors, function(key, value) {
                            errorHtml += '<li>' + value[0] + '</li>';
                        });
                        errorHtml += '</ul></div>';
                        form.find('.modal-body').prepend(errorHtml);
                    }
                });
            });

        });
    </script>
@endsection