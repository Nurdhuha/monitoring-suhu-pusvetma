<?php

namespace App\Http\Controllers;

use App\Models\Device; // Import the Device model
use App\Models\DataSuhu; // Import the DataSuhu model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Import the Log facade
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Auth::user()->isSuperAdmin()) {
            return redirect()->route('superadmin.home');
        }

        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.home');
        }

        $devices = Device::all(); // Fetch all devices
        $dataSuhu = DataSuhu::with('device')->latest()->take(10)->get(); // Fetch latest 10 temperature readings
        return view('home', compact('devices', 'dataSuhu')); // Pass both to the view
    }

    public function dashboard()
    {
        if (Auth::user()->isSuperAdmin()) {
            return redirect()->route('superadmin.home');
        } elseif (Auth::user()->isAdmin()) {
            return redirect()->route('admin.home');
        } else {
            return redirect()->route('home');
        }
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
                    $dataset = [
                        'label' => $device->name . ' (' . $device->location . ')',
                        'data' => [],
                        'device_id' => $deviceId, // Add device_id for frontend color mapping
                        'fill' => false,
                    ];

                    foreach ($readings as $reading) {
                        $dataset['data'][] = [
                            'x' => $reading->created_at->toIso8601String(), // Use ISO 8601 format
                            'y' => $reading->temperature,
                            'section' => $reading->section, // Add section data
                        ];
                    }
                    $datasets[] = $dataset;
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
