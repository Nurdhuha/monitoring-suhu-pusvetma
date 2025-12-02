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

class DataSuhuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DataSuhu::with(['device', 'user'])->latest();

        $selectedDeviceId = $request->input('device_id');

        if ($selectedDeviceId) {
            $query->where('device_id', $selectedDeviceId);
        }

        $dataSuhu = $query->paginate(10)->onEachSide(0)->appends($request->query());
        $devices = Device::all();

        $viewData = [
            'dataSuhu' => $dataSuhu,
            'devices' => $devices,
            'selectedDeviceId' => $selectedDeviceId,
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
