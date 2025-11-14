import './bootstrap';

// Define a global function to initialize the home page scripts
window.initializeHomePage = function(routeUrl) {
    console.log("initializeHomePage called with routeUrl:", routeUrl);

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

        function fetchDataAndRenderChart(device_ids) {
            console.log("fetchDataAndRenderChart called with device_ids:", device_ids);

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
                url: routeUrl, // Use the passed routeUrl
                method: 'GET',
                data: { device_ids: device_ids },
                success: function(response) {
                    console.log("AJAX success. Response:", response);
                    if (!response || !response.labels || !response.datasets) {
                        console.error("Invalid response format from API.", response);
                        alert("Invalid data received from server.");
                        return;
                    }

                    const labels = response.labels;
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
                    console.log("Chart labels:", labels);
                    console.log("Chart datasets:", datasets);

                    temperatureChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    type: 'time',
                                    time: {
                                        unit: 'hour',
                                        tooltipFormat: 'MMM D, YYYY h:mm A',
                                        displayFormats: {
                                            hour: 'h:mm A'
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
                    alert("Failed to fetch temperature data. Please check console for details.");
                }
            });
        }

        // Initial load (if any devices are pre-selected or default)
        const initialDeviceIds = $('#device_selector').val();
        console.log("Initial selected device IDs:", initialDeviceIds);
        if (initialDeviceIds && initialDeviceIds.length > 0) {
            fetchDataAndRenderChart(initialDeviceIds);
        } else {
            console.log("No initial devices selected for chart.");
        }

        // Event listener for device selection change
        $('#device_selector').on('change', function() {
            const selectedDeviceIds = $(this).val();
            console.log("Device selection changed to:", selectedDeviceIds);
            fetchDataAndRenderChart(selectedDeviceIds);
        });
    });
};
