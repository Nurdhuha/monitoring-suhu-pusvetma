@extends('layouts.userlayout')

@push('styles')
<style>
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        opacity: 0;
        animation: fade-in-up 0.6s ease-out forwards;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .fa-spinner {
        animation: spin 1s linear infinite;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    @auth
    <div class="flex justify-end items-center mb-4 space-x-2 animate-fade-in-up" style="animation-delay: 0.1s;">
        @if(Auth::user()->isSuperAdmin())
            <a href="{{ route('superadmin.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Back to Dashboard') }}
            </a>
        @endif
        <a class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150" href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            {{ __('Logout') }}
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
    </div>
    @endauth

    <div class="text-center mb-8 animate-fade-in-up" style="animation-delay: 0.2s;">
        <h2 class="text-3xl font-bold text-gray-900 mb-3">{{ __('Monitoring Suhu Thermohygrometer') }}</h2>
        <p class="text-lg text-gray-600">{{ __('Pilih satu atau lebih perangkat di bawah untuk melihat grafik suhu secara real-time.') }}</p>
    </div>

    <div class="mb-8 animate-fade-in-up" style="animation-delay: 0.3s;">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg transition duration-300 ease-in-out hover:shadow-xl hover:transform hover:-translate-y-1">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 text-lg font-medium text-gray-900">{{ __('Temperature Dashboard') }}</div>
            <div class="p-6">
                @if (session('status'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Stat Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 animate-fade-in-up" style="animation-delay: 0.4s;">
                    <div class="p-4 bg-blue-50 rounded-lg text-center transition-transform duration-300 hover:scale-105">
                        <h4 class="text-sm font-medium text-blue-600">Suhu Terendah</h4>
                        <p id="stat-min-temp" class="text-2xl font-bold text-blue-800">- °C</p>
                    </div>
                    <div class="p-4 bg-green-50 rounded-lg text-center transition-transform duration-300 hover:scale-105">
                        <h4 class="text-sm font-medium text-green-600">Suhu Rata-rata</h4>
                        <p id="stat-avg-temp" class="text-2xl font-bold text-green-800">- °C</p>
                    </div>
                    <div class="p-4 bg-red-50 rounded-lg text-center transition-transform duration-300 hover:scale-105">
                        <h4 class="text-sm font-medium text-red-600">Suhu Tertinggi</h4>
                        <p id="stat-max-temp" class="text-2xl font-bold text-red-800">- °C</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="device_selector" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Select Devices:') }}</label>
                        <select class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm" id="device_selector" multiple>
                            @foreach($devices as $device)
                                <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->location }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="time_range_selector" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Select Time Range:') }}</label>
                        <select class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm" id="time_range_selector">
                            <option value="5_days">5 Hari</option>
                            <option value="1_month">1 Bulan</option>
                            <option value="6_months">6 Bulan</option>
                            <option value="1_year">1 Tahun</option>
                            <option value="5_years">5 Tahun</option>
                            <option value="all_time" selected>Semua</option>
                        </select>
                    </div>
                </div>

                <div class="relative min-h-[300px] h-[60vh] w-full">
                    <div id="chartLoadingIndicator" class="absolute inset-0 flex-col items-center justify-center bg-white bg-opacity-80 z-10 rounded-lg" style="display: none;">
                        <i class="fas fa-spinner text-4xl text-gray-500"></i>
                        <p class="mt-3 text-lg text-gray-600">Memuat data grafik...</p>
                    </div>
                    <canvas id="temperatureChart"></canvas>
                    <div id="noChartDataMessage" class="absolute inset-0 flex items-center justify-center text-gray-500 transition-opacity duration-300">
                        <p class="text-lg">{{ __('Pilih alat untuk menampilkan grafik suhu') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="animate-fade-in-up" style="animation-delay: 0.5s;">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg transition duration-300 ease-in-out hover:shadow-xl hover:transform hover:-translate-y-1">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 text-lg font-medium text-gray-900">{{ __('Recent Temperature Readings') }}</div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">#</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Device</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Section</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Temperature</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Recorded At</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($dataSuhu as $reading)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $reading->device->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $reading->section == 'pagi' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($reading->section) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $reading->temperature }} °C</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $reading->created_at->format('d M Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No recent temperature readings found.</td></tr>
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
$(document).ready(function() {
    $('#device_selector').select2({
        placeholder: "Select one or more devices",
        allowClear: true
    });

    const ctx = document.getElementById('temperatureChart').getContext('2d');
    if (!ctx) return;

    let temperatureChart;
    const deviceColors = {};
    const availableColors = ['rgb(255, 99, 132)', 'rgb(54, 162, 235)', 'rgb(255, 206, 86)', 'rgb(75, 192, 192)', 'rgb(153, 102, 255)', 'rgb(255, 159, 64)'];
    let colorIndex = 0;

    function getDeviceColor(deviceId) {
        if (!deviceColors[deviceId]) {
            deviceColors[deviceId] = availableColors[colorIndex % availableColors.length];
            colorIndex++;
        }
        return deviceColors[deviceId];
    }

    function getTimeUnit(timeRange) {
        switch (timeRange) {
            case '5_days': case '1_month': return 'day';
            case '6_months': case '1_year': return 'month';
            case '5_years': return 'year';
            default: return 'month';
        }
    }

    function updateStatCards(datasets) {
        try {
            // console.log("Updating stat cards with datasets:", datasets);

            if (!datasets || datasets.length === 0) {
                $('#stat-min-temp, #stat-avg-temp, #stat-max-temp').text('- °C');
                // console.log("No datasets, clearing stats.");
                return;
            }

            let overallMin = Infinity;
            let overallMax = -Infinity;
            const deviceAverages = [];
            let allPointsCount = 0;

            datasets.forEach(dataset => {
                if (dataset.data && dataset.data.length > 0) {
                    const temps = dataset.data.map(p => parseFloat(p.y));
                    // console.log(`Temps for ${dataset.label}:`, temps);

                    const deviceMin = Math.min(...temps);
                    const deviceMax = Math.max(...temps);
                    const deviceSum = temps.reduce((a, b) => a + b, 0);
                    const deviceAvg = deviceSum / temps.length;
                    // console.log(`Stats for ${dataset.label}: Min=${deviceMin}, Max=${deviceMax}, Avg=${deviceAvg}`);

                    overallMin = Math.min(overallMin, deviceMin);
                    overallMax = Math.max(overallMax, deviceMax);
                    deviceAverages.push(deviceAvg);
                    allPointsCount += temps.length;
                }
            });

            // console.log("All points count:", allPointsCount);
            // console.log("Device averages:", deviceAverages);

            if (allPointsCount === 0) {
                $('#stat-min-temp, #stat-avg-temp, #stat-max-temp').text('- °C');
                // console.log("No data points found across all datasets, clearing stats.");
                return;
            }
            
            const finalAvg = deviceAverages.reduce((a, b) => a + b, 0) / deviceAverages.length;
            // console.log("Final calculated average:", finalAvg);

            $('#stat-min-temp').text(overallMin.toFixed(2) + ' °C');
            $('#stat-avg-temp').text(finalAvg.toFixed(2) + ' °C');
            $('#stat-max-temp').text(overallMax.toFixed(2) + ' °C');
            // console.log("Stats updated successfully.");

        } catch (error) {
            console.error("Error in updateStatCards:", error);
            $('#stat-min-temp, #stat-avg-temp, #stat-max-temp').text('Error');
        }
    }

    function fetchDataAndRenderChart(device_ids, time_range) {
        const chartCanvas = $('#temperatureChart');
        const noDataMessage = $('#noChartDataMessage');
        const loadingIndicator = $('#chartLoadingIndicator');

        if (temperatureChart) {
            temperatureChart.destroy();
        }

        if (!device_ids || device_ids.length === 0) {
            chartCanvas.hide();
            noDataMessage.show();
            loadingIndicator.hide();
            updateStatCards([]); // Reset stats
            return;
        }

        chartCanvas.hide();
        noDataMessage.hide();
        loadingIndicator.css('display', 'flex'); // Use flex to center content

        $.ajax({
            url: '{{ route('get.temperature.data') }}',
            method: 'GET',
            data: { device_ids: device_ids, time_range: time_range },
            success: function(response) {
                if (!response || !response.datasets) {
                    console.error("Invalid response format from API.", response);
                    noDataMessage.show();
                    updateStatCards([]);
                    return;
                }

                const datasets = response.datasets.map(dataset => ({
                    label: dataset.label,
                    data: dataset.data,
                    borderColor: getDeviceColor(dataset.device_id),
                    backgroundColor: getDeviceColor(dataset.device_id),
                    fill: false,
                    tension: 0.1,
                    borderWidth: 1.5,
                    pointRadius: 2,
                    pointHoverRadius: 5,
                    pointBorderWidth: 0,
                    pointBackgroundColor: ctx => {
                        const p = ctx.raw;
                        return p.section === 'pagi' ? 'rgb(54, 162, 235)' : 'rgb(255, 206, 86)';
                    }
                }));

                updateStatCards(datasets);
                chartCanvas.fadeIn(500);

                temperatureChart = new Chart(ctx, {
                    type: 'line',
                    data: { datasets: datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 800, easing: 'easeInOutQuart' },
                        scales: {
                            x: { type: 'time', time: { unit: getTimeUnit(time_range), tooltipFormat: 'MMM D, YYYY h:mm A' }, title: { display: true, text: 'Time' } },
                            y: { title: { display: true, text: 'Temperature (°C)' } }
                        },
                        plugins: { tooltip: { mode: 'index', intersect: false, bodySpacing: 8, titleSpacing: 6, } },
                        interaction: { mode: 'nearest', axis: 'x', intersect: false }
                    }
                });
            },
            error: function(xhr) {
                console.error("Error fetching data:", xhr.responseText);
                noDataMessage.show().find('p').text('Gagal memuat data. Silakan coba lagi.');
                updateStatCards([]);
            },
            complete: function() {
                loadingIndicator.hide();
            }
        });
    }

    // Initial Load Logic
    let initialDeviceIds = $('#device_selector').val();
    const initialTimeRange = $('#time_range_selector').val();
    
    if ((!initialDeviceIds || initialDeviceIds.length === 0) && $('#device_selector option').length > 0) {
        const firstDeviceId = $('#device_selector option:first').val();
        $('#device_selector').val(firstDeviceId); // Set value but don't trigger change yet
        initialDeviceIds = [firstDeviceId]; // Manually set it for the initial call
    }
    
    if (initialDeviceIds && initialDeviceIds.length > 0) {
        fetchDataAndRenderChart(initialDeviceIds, initialTimeRange);
    } else {
        // If still no devices, ensure stats are cleared
        updateStatCards([]);
    }

    $('#device_selector, #time_range_selector').on('change', function() {
        const selectedDeviceIds = $('#device_selector').val();
        const selectedTimeRange = $('#time_range_selector').val();
        fetchDataAndRenderChart(selectedDeviceIds, selectedTimeRange);
    });
});
</script>
@endsection

        