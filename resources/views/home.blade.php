@extends('layouts.userlayout')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center mb-4">
        <div class="col-md-10 text-center">
            <h2 class="mb-3">{{ __('Monitoring Suhu Thermohygrometer') }}</h2>
            <p class="lead text-muted">
                {{ __('Pilih satu atau lebih perangkat di bawah untuk melihat grafik suhu secara real-time.') }}
            </p>
        </div>
    </div>

    <div class="row justify-content-center mb-4">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">{{ __('Temperature Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="form-group mb-4">
                        <label for="device_selector" class="form-label">{{ __('Select Devices:') }}</label>
                        <select class="form-control" id="device_selector" multiple>
                            @foreach($devices as $device)
                                <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->location }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="chart-container" style="position: relative; min-height: 300px; height: 60vh; width: 100%;">
                        <canvas id="temperatureChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">{{ __('Recent Temperature Readings') }}</div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Device</th>
                                <th>Section</th>
                                <th>Temperature</th>
                                <th>Recorded At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dataSuhu as $reading)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $reading->device->name ?? 'N/A' }}</td>
                                    <td><span class="badge {{ $reading->section == 'pagi' ? 'bg-info' : 'bg-warning' }}">{{ ucfirst($reading->section) }}</span></td>
                                    <td>{{ $reading->temperature }} °C</td>
                                    <td>{{ $reading->created_at->format('d M Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No recent temperature readings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        // Pass the route URL to the main initialization function from app.js
        window.initializeHomePage('{{ route('get.temperature.data') }}');
    </script>
@endsection
