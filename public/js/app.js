// main JavaScript for the user home page
window.initializeHomePage = function(temperatureDataRoute) {
    console.log("initializeHomePage function called with route:", temperatureDataRoute);

    console.log("home.blade.php scripts loaded.");
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

        function fetchDataAndRenderChart(device_ids) {
            console.log("fetchDataAndRenderChart called with device_ids:", device_ids);
            if (temperatureChart) {
                temperatureChart.destroy(); // Destroy previous chart instance
                temperatureChart = null; // Clear the reference
                console.log("Previous chart instance destroyed and reference cleared.");
            }

            if (device_ids.length === 0) {
                console.log("No devices selected. Clearing canvas.");
                ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
                return;
            }

            console.log("Selected device IDs for AJAX call:", device_ids);
            console.log("Making AJAX call to fetch temperature data...");
            $.ajax({
                url: temperatureDataRoute, // Use the passed route here
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
                    const datasets = response.datasets.map(dataset => ({
                        label: dataset.label,
                        data: dataset.data,
                        borderColor: dataset.borderColor,
                        backgroundColor: dataset.borderColor, // Use same color for background
                        fill: false,
                        tension: 0.1
                    }));
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
