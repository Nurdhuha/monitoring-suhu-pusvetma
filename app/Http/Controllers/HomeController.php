<?php

namespace App\Http\Controllers;

use App\Models\Device; // Import the Device model
use App\Models\DataSuhu; // Import the DataSuhu model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Import the Log facade

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $devices = Device::all(); // Fetch all devices
        $dataSuhu = DataSuhu::with('device')->latest()->take(10)->get(); // Fetch latest 10 temperature readings
        return view('home', compact('devices', 'dataSuhu')); // Pass both to the view
    }

    /**
     * Get temperature data for Chart.js.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTemperatureData(Request $request)
    {
        try {
            $deviceIds = $request->input('device_ids');
            Log::info('getTemperatureData requested with device_ids: ' . json_encode($deviceIds));

            if (empty($deviceIds)) {
                return response()->json(['labels' => [], 'datasets' => []]);
            }

            // Ensure deviceIds is an array
            if (!is_array($deviceIds)) {
                $deviceIds = [$deviceIds];
            }

            $data = DataSuhu::whereIn('device_id', $deviceIds)
                            ->orderBy('created_at')
                            ->get()
                            ->groupBy('device_id');

            $labels = [];
            $datasets = [];
            $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']; // Example colors

            foreach ($data as $deviceId => $readings) {
                $device = Device::find($deviceId);
                if ($device) {
                    $dataset = [
                        'label' => $device->name . ' (' . $device->location . ')',
                        'data' => [],
                        'borderColor' => $colors[array_rand($colors)], // Assign a random color
                        'fill' => false,
                    ];

                    foreach ($readings as $reading) {
                        $labels[] = $reading->created_at->format('Y-m-d H:i'); // Collect all timestamps for labels
                        $dataset['data'][] = [
                            'x' => $reading->created_at->format('Y-m-d H:i'),
                            'y' => $reading->temperature,
                        ];
                    }
                    $datasets[] = $dataset;
                }
            }

            // Ensure unique and sorted labels
            $labels = array_unique($labels);
            sort($labels);

            // Reformat datasets to match sorted labels
            foreach ($datasets as &$dataset) {
                $newData = [];
                foreach ($labels as $label) {
                    $found = false;
                    foreach ($dataset['data'] as $item) {
                        if ($item['x'] === $label) {
                            $newData[] = $item['y'];
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $newData[] = null; // No data for this timestamp
                    }
                }
                $dataset['data'] = $newData;
            }

            Log::info('getTemperatureData response: ' . json_encode(['labels' => $labels, 'datasets' => $datasets]));
            return response()->json(['labels' => $labels, 'datasets' => $datasets]);

        } catch (\Exception $e) {
            Log::error('Error in getTemperatureData: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Failed to fetch temperature data.', 'message' => $e->getMessage()], 500);
        }
    }
}
