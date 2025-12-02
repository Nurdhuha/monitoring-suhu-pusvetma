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
            $timeRange = $request->input('time_range', 'all_time'); // Default to 'all_time'
            // Log::info('getTemperatureData requested with device_ids: ' . json_encode($deviceIds) . ' and time_range: ' . $timeRange);

            if (empty($deviceIds)) {
                return response()->json(['datasets' => []]);
            }

            // Ensure deviceIds is an array
            if (!is_array($deviceIds)) {
                $deviceIds = [$deviceIds];
            }

            $query = DataSuhu::whereIn('device_id', $deviceIds);

            // Add time range condition
            switch ($timeRange) {
                case '5_days':
                    $query->where('created_at', '>=', now()->subDays(4));
                    break;
                case '1_month':
                    $query->where('created_at', '>=', now()->subMonth());
                    break;
                case '6_months':
                    $query->where('created_at', '>=', now()->subMonths(6));
                    break;
                case '1_year':
                    $query->where('created_at', '>=', now()->subYear());
                    break;
                case '5_years':
                    $query->where('created_at', '>=', now()->subYears(5));
                    break;
                case 'all_time':
                default:
                    // No time range restriction
                    break;
            }

            $data = $query->orderBy('created_at')
                            ->get()
                            ->groupBy('device_id');

            $datasets = [];
            foreach ($data as $deviceId => $readings) {
                $device = Device::find($deviceId);
                if ($device) {
                    $pagiData = [];
                    $soreData = [];

                    foreach ($readings as $reading) {
                        if ($reading->section === 'pagi') {
                            $pagiData[] = [
                                'x' => $reading->created_at->toIso8601String(),
                                'y' => $reading->temperature,
                            ];
                        } else {
                            $soreData[] = [
                                'x' => $reading->created_at->toIso8601String(),
                                'y' => $reading->temperature,
                            ];
                        }
                    }

                    if (!empty($pagiData)) {
                        $datasets[] = [
                            'label' => $device->name . ' (' . $device->location . ') - Pagi',
                            'data' => $pagiData,
                            'borderColor' => 'rgb(54, 162, 235)',
                            'backgroundColor' => 'rgb(54, 162, 235)',
                            'fill' => false,
                        ];
                    }

                    if (!empty($soreData)) {
                        $datasets[] = [
                            'label' => $device->name . ' (' . $device->location . ') - Sore',
                            'data' => $soreData,
                            'borderColor' => 'rgb(255, 206, 86)',
                            'backgroundColor' => 'rgb(255, 206, 86)',
                            'fill' => false,
                        ];
                    }
                }
            }

            // Log::info('getTemperatureData response: ' . json_encode(['datasets' => $datasets]));
            return response()->json(['datasets' => $datasets]);

        } catch (\Exception $e) {
            // Log::error('Error in getTemperatureData: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Failed to fetch temperature data.',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
