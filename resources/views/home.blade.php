@extends('layouts.userlayout')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    @auth
    <div class="flex justify-end mb-4">
        <a class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150" href="{{ route('logout') }}"
           onclick="event.preventDefault();
                         document.getElementById('logout-form').submit();">
            {{ __('Logout') }}
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
    @endauth

    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-3">{{ __('Monitoring Suhu Thermohygrometer') }}</h2>
        <p class="text-lg text-gray-600">
            {{ __('Pilih satu atau lebih perangkat di bawah untuk melihat grafik suhu secara real-time.') }}
        </p>
    </div>

    <div class="mb-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 text-lg font-medium text-gray-900">
                {{ __('Temperature Dashboard') }}
            </div>

            <div class="p-6">
                @if (session('status'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('status') }}</span>
                    </div>
                @endif

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
                    <canvas id="temperatureChart"></canvas>
                    <div id="noChartDataMessage" class="absolute inset-0 flex items-center justify-center text-gray-500">
                        <p class="text-lg">{{ __('Pilih alat untuk menampilkan grafik suhu') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 text-lg font-medium text-gray-900">
                {{ __('Recent Temperature Readings') }}
            </div>
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
                                <tr class="hover:bg-gray-50">
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
                                <tr>
                                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No recent temperature readings found.</td>
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
        $(document).ready(function() {
            console.log("Document ready. Initializing Select2 and Chart.js.");

            // Initialize Select2
            $('#device_selector').select2({
                placeholder: "Select one or more devices",
                allowClear: true
            });
            console.log("Select2 initialized.");

            const ctx = document.getElementById('temperatureChart').getContext('2d');
            if (!ctx) {
                console.error("Canvas context not found!");
                return;
            }
            console.log("Canvas context obtained.");

            let temperatureChart;
            const deviceColors = {}; // Store device_id -> color mapping
            const availableColors = [
                'rgb(255, 99, 132)',  // Red
                'rgb(54, 162, 235)',  // Blue
                'rgb(255, 206, 86)',  // Yellow
                'rgb(75, 192, 192)',  // Green
                'rgb(153, 102, 255)', // Purple
                'rgb(255, 159, 64)',  // Orange
                'rgb(201, 203, 207)', // Grey
                'rgb(70, 130, 180)',  // Steel Blue
                'rgb(60, 179, 113)'   // Medium Sea Green
            ];
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
                    case '5_days':
                        return 'day';
                    case '1_month':
                        return 'day';
                    case '6_months':
                        return 'month';
                    case '1_year':
                        return 'month';
                    case '5_years':
                        return 'year';
                    case 'all_time':
                    default:
                        return 'month'; // Default to month for all_time
                }
            }

            function fetchDataAndRenderChart(device_ids, time_range) {
                console.log("fetchDataAndRenderChart called with device_ids:", device_ids, "and time_range:", time_range);

                const chartCanvas = $('#temperatureChart');
                const noDataMessage = $('#noChartDataMessage');

                if (temperatureChart) {
                    temperatureChart.destroy(); // Destroy previous chart instance
                    console.log("Previous chart instance destroyed.");
                }

                if (device_ids.length === 0) {
                    console.log("No devices selected. Displaying message.");
                    chartCanvas.hide();
                    noDataMessage.show();
                    return;
                } else {
                    chartCanvas.show();
                    noDataMessage.hide();
                }

                console.log("Making AJAX call to fetch temperature data...");
                $.ajax({
                    url: '{{ route('get.temperature.data') }}', // Use the passed routeUrl
                    method: 'GET',
                    data: {
                        device_ids: device_ids,
                        time_range: time_range
                    },
                    success: function(response) {
                        console.log("AJAX success. Response:", response);
                        if (!response || !response.datasets) {
                            console.error("Invalid response format from API.", response);
                            alert("Invalid data received from server.");
                            return;
                        }

                        const datasets = response.datasets.map(dataset => {
                            const color = getDeviceColor(dataset.device_id); // Get persistent color
                            return {
                                label: dataset.label,
                                data: dataset.data,
                                borderColor: color,
                                backgroundColor: color, // Use same color for background
                                fill: false,
                                tension: 0.1
                            };
                        });
                        console.log("Chart datasets:", datasets);

                        const timeUnit = getTimeUnit(time_range);

                        temperatureChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                datasets: datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    x: {
                                        type: 'time',
                                        time: {
                                            unit: timeUnit,
                                            tooltipFormat: 'MMM D, YYYY h:mm A',
                                            displayFormats: {
                                                hour: 'h:mm A',
                                                day: 'MMM D',
                                                month: 'MMM YYYY',
                                                year: 'YYYY'
                                            }
                                        },
                                        title: {
                                            display: true,
                                            text: 'Time'
                                        }
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                            text: 'Temperature (°C)'
                                        }
                                    }
                                },
                                plugins: {
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false
                                    }
                                },
                                hover: {
                                    mode: 'nearest',
                                    intersect: true
                                }
                            }
                        });
                        console.log("Chart initialized successfully.");
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching data:", error, xhr.responseText);
                        let errorMessage = "Failed to fetch temperature data. Please check console for details.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                            if (xhr.responseJSON.trace) {
                                console.error("Backend Trace:", xhr.responseJSON.trace);
                            }
                        }
                        alert(errorMessage);
                    }
                });
            }

            // Initial load (if any devices are pre-selected or default)
            const initialDeviceIds = $('#device_selector').val();
            const initialTimeRange = $('#time_range_selector').val();
            console.log("Initial selected device IDs:", initialDeviceIds, "and time range:", initialTimeRange);
            if (initialDeviceIds && initialDeviceIds.length > 0) {
                fetchDataAndRenderChart(initialDeviceIds, initialTimeRange);
            } else {
                console.log("No initial devices selected for chart.");
            }

            // Event listener for device selection change
            $('#device_selector').on('change', function() {
                const selectedDeviceIds = $(this).val();
                const selectedTimeRange = $('#time_range_selector').val();
                console.log("Device selection changed to:", selectedDeviceIds, "with time range:", selectedTimeRange);
                fetchDataAndRenderChart(selectedDeviceIds, selectedTimeRange);
            });

            // Event listener for time range selection change
            $('#time_range_selector').on('change', function() {
                const selectedDeviceIds = $('#device_selector').val();
                const selectedTimeRange = $(this).val();
                console.log("Time range selection changed to:", selectedTimeRange, "with devices:", selectedDeviceIds);
                if (selectedDeviceIds && selectedDeviceIds.length > 0) {
                    fetchDataAndRenderChart(selectedDeviceIds, selectedTimeRange);
                }
            });
        });
    </script>
@endsection
