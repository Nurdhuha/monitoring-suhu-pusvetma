<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $devices = Device::with('user')->latest()->paginate(10);

        if (Auth::user()->isSuperAdmin()) {
            return view('superadmin.devices.index', compact('devices'));
        }

        return view('admin.devices.index', compact('devices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->isSuperAdmin()) {
            return view('superadmin.devices.create');
        }

        return view('admin.devices.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $device = new Device($request->all());
        $device->user_id = Auth::id();
        $device->save();

        $redirectRoute = Auth::user()->isSuperAdmin() ? 'superadmin.devices.index' : 'admin.devices.index';

        return redirect()->route($redirectRoute)
                         ->with('success', 'Device created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Device $device)
    {
        return view('admin.devices.show', compact('device'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Device $device)
    {
        return response()->json($device);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Device $device)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
            ]);

            $device->update($validatedData);

            return response()->json(['success' => 'Device updated successfully.']);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Device $device)
    {
        $device->delete();

        $redirectRoute = Auth::user()->isSuperAdmin() ? 'superadmin.devices.index' : 'admin.devices.index';

        return redirect()->route($redirectRoute)
                         ->with('success', 'Device deleted successfully.');
    }
}
