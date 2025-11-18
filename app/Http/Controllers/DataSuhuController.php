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
    public function index()
    {
        $dataSuhu = DataSuhu::with(['device', 'user'])->latest()->paginate(10);
        $devices = Device::all(); // Fetch all devices

        if (Auth::user()->isSuperAdmin()) {
            return view('superadmin.data-suhu.index', compact('dataSuhu', 'devices'));
        }

        return view('admin.data-suhu.index', compact('dataSuhu', 'devices'));
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
                'section' => 'required|in:pagi,siang',
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
                'section' => 'required|in:pagi,siang',
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
        Log::info('Entering destroy method for DataSuhu ID: ' . $data_suhu->id);

        try {
            $deleted = $data_suhu->delete();

            if ($deleted) {
                Log::info('Successfully deleted DataSuhu ID: ' . $data_suhu->id);
                return response()->json(['success' => 'Data Suhu deleted successfully.']);
            } else {
                Log::error('Failed to delete DataSuhu ID: ' . $data_suhu->id . '. The delete() method returned false.');
                return response()->json(['error' => 'Failed to delete the record. The delete method returned false.'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Exception caught while deleting DataSuhu ID: ' . $data_suhu->id . '. Message: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected server error occurred while attempting to delete the record.'], 500);
        }
    }

    /**
     * Handle the download request for temperature data in Excel format.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadExcel(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        return Excel::download(new DataSuhuExport($startDate, $endDate), 'data-suhu.xlsx');
    }
}
