<?php

namespace App\Http\Controllers;

use App\Models\DataSuhu;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // Add this import
use App\Exports\DataSuhuExport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DataSuhuImport;

class DataSuhuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
        public function index(Request $request)
        {
            // Get selected device IDs for the chart (can be multiple)
            $selectedChartDeviceIds = (array) $request->input('chart_device_ids', []);
    
            // Get selected device ID for the list (can only be one)
            $selectedListDeviceId = $request->input('list_device_id');
    
            $timeRange = $request->input('time_range', '5_days');
    
            // Query for the data list
            $listQuery = DataSuhu::with(['device', 'user'])->latest();
            if ($selectedListDeviceId) {
                $listQuery->where('device_id', $selectedListDeviceId);
            }
    
            $dataSuhu = $listQuery->paginate(10)->appends($request->query());
            $devices = Device::all();
    
            // Query for the chart
            $chartQuery = DataSuhu::query()
                ->with('device') // Eager load device information
                ->select('device_id', 'temperature', 'section', 'created_at')
                ->orderBy('created_at');
    
            // If chart device IDs are selected, use them. Otherwise, if the list device ID is selected, use it for the chart too.
            $chartDeviceIds = !empty($selectedChartDeviceIds) ? $selectedChartDeviceIds : ($selectedListDeviceId ? [$selectedListDeviceId] : []);
    
            if (!empty($chartDeviceIds)) {
                $chartQuery->whereIn('device_id', $chartDeviceIds);
            }
    
            switch ($timeRange) {
                case '5_days':
                    $chartQuery->where('created_at', '>=', now()->subDays(5));
                    break;
                case '1_month':
                    $chartQuery->where('created_at', '>=', now()->subMonth());
                    break;
                case '6_months':
                    $chartQuery->where('created_at', '>=', now()->subMonths(6));
                    break;
                case '1_year':
                    $chartQuery->where('created_at', '>=', now()->subYear());
                    break;
                case 'all_time':
                    // No time range restriction
                    break;
                default:
                    $chartQuery->where('created_at', '>=', now()->subDays(5));
                    break;
            }
    
            $readings = $chartQuery->get();
    
            $datasets = [];
            $deviceColors = [];
            $colors = [
                'rgb(255, 99, 132)',  // Red
                'rgb(54, 162, 235)',  // Blue
                'rgb(255, 206, 86)',  // Yellow
                'rgb(75, 192, 192)',  // Green
                'rgb(153, 102, 255)', // Purple
                'rgb(255, 159, 64)',  // Orange
                'rgb(70, 130, 180)',  // Steel Blue
                'rgb(60, 179, 113)'   // Medium Sea Green
            ];
            $colorIndex = 0;
    
            $groupedReadings = $readings->groupBy('device_id');
    
            foreach ($groupedReadings as $deviceId => $deviceReadings) {
                $device = $devices->find($deviceId);
                if ($device) {
                    if (!isset($deviceColors[$deviceId])) {
                        $deviceColors[$deviceId] = $colors[$colorIndex % count($colors)];
                        $colorIndex++;
                    }
    
                    $datasets[] = [
                        'label' => $device->name . ' (' . $device->location . ')',
                        'data' => $deviceReadings->map(function ($item) {
                            return [
                                'x' => $item->created_at->toIso8601String(),
                                'y' => $item->temperature,
                                'section' => $item->section,
                            ];
                        })->values(),
                        'borderColor' => $deviceColors[$deviceId],
                        'backgroundColor' => 'rgba(201, 203, 207, 0.5)',
                        'fill' => false,
                        'tension' => 0.1,
                    ];
                }
            }
    
            $chartData = [
                'datasets' => $datasets,
            ];
    
            $viewData = [
                'dataSuhu' => $dataSuhu,
                'devices' => $devices,
                'selectedChartDeviceIds' => $chartDeviceIds, // Pass the effective IDs to the view
                'selectedListDeviceId' => $selectedListDeviceId,
                'timeRange' => $timeRange,
                'chartData' => $chartData,
            ];
    
            if (Auth::user()->isSuperAdmin()) {
                return view('superadmin.data-suhu.index', $viewData);
            }
    
            return view('admin.data-suhu.index', $viewData);
        }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $devices = Device::all();
        return view('admin.data-suhu.create', compact('devices'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'device_id' => 'required|exists:devices,id',
                'section' => 'required|in:pagi,sore',
                'temperature' => 'required|numeric',
            ]);

            // Add the authenticated user's ID
            $validatedData['user_id'] = Auth::id();

            DataSuhu::create($validatedData);

            return response()->json(['success' => 'Data Suhu created successfully.']);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DataSuhu $data_suhu)
    {
        return response()->json($data_suhu);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DataSuhu $data_suhu)
    {
        return response()->json($data_suhu->load('device')); // Load device relationship for display
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DataSuhu $data_suhu)
    {
        try {
            $validatedData = $request->validate([
                'device_id' => 'required|exists:devices,id',
                'section' => 'required|in:pagi,sore',
                'temperature' => 'required|numeric',
            ]);

            $data_suhu->update($validatedData);

            return response()->json(['success' => 'Data Suhu updated successfully.']);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DataSuhu $data_suhu)
    {
        try {
            if ($data_suhu->delete()) {
                return redirect()->back()->with('success', 'Data Suhu deleted successfully.');
            }
            return redirect()->back()->with('error', 'Failed to delete the record.');
        } catch (\Exception $e) {
            Log::error('Error deleting DataSuhu: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An unexpected error occurred while deleting the record.');
        }
    }

    /**
     * Handle the download request for temperature data in Excel format.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadExcel(Request $request)
    {
        $deviceId = $request->input('device_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $filename = 'data-suhu';
        if ($deviceId) {
            $device = Device::find($deviceId);
            if ($device) {
                $filename .= '_' . str_replace(' ', '-', $device->name);
            }
        }
        if ($startDate && $endDate) {
            $filename .= '_' . $startDate . '_to_' . $endDate;
        } elseif ($startDate) {
            $filename .= '_' . $startDate . '_to_present';
        } elseif ($endDate) {
            $filename .= '_until_' . $endDate;
        }
        
        if (!$startDate && !$endDate && !$deviceId) {
            $filename .= '_all-time';
        }
        
        $filename .= '.xlsx';

        return Excel::download(new DataSuhuExport($deviceId, $startDate, $endDate), $filename);
    }
}
