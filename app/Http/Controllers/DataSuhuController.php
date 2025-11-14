<?php

namespace App\Http\Controllers;

use App\Models\DataSuhu;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // Add this import
use App\Exports\DataSuhuExport;
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
    public function show(DataSuhu $dataSuhu)
    {
        return response()->json($dataSuhu);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DataSuhu $dataSuhu)
    {
        return response()->json($dataSuhu->load('device')); // Load device relationship for display
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DataSuhu $dataSuhu)
    {
        try {
            $validatedData = $request->validate([
                'device_id' => 'required|exists:devices,id',
                'section' => 'required|in:pagi,siang',
                'temperature' => 'required|numeric',
            ]);

            $dataSuhu->update($validatedData);

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
    public function destroy(DataSuhu $dataSuhu)
    {
        try {
            $dataSuhu->delete();
            return response()->json(['success' => 'Data Suhu deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    /**
     * Handle the download request for temperature data in Excel format.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadExcel()
    {
        return Excel::download(new DataSuhuExport, 'data-suhu.xlsx');
    }
}
