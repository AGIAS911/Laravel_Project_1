<?php

namespace App\Http\Controllers;

use App\Models\DriverProfile;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vehicles = Vehicle::with(['vehicleType', 'driver'])->latest()->paginate(10);

        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vehicleTypes = VehicleType::where('is_active', true)->get();
        $drivers = DriverProfile::all();

        return view('vehicles.create', compact('vehicleTypes', 'drivers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'driver_id' => 'required|exists:driver_profiles,id',
            'name' => 'required|string|max:150',
            'plate_number' => 'required|string|max:30|unique:vehicles,plate_number',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'capacity_kg' => 'required|in:small,medium,large',
            'capacity_m3' => 'required|in:small,medium,large',
            'base_price' => 'required|numeric|min:0',
            'price_per_km' => 'required|numeric|min:0',
            'status' => 'nullable|in:active,inactive,maintenance',
        ]);

        if ($request->hasFile('image')) {
            $validatedData['image'] = $this->storeImage($request->file('image'));
        }
        $validatedData['status'] = $validatedData['status'] ?? 'active';

        Vehicle::create($validatedData);

        return redirect()->route('vehicles.index')->with('success', 'vehicle has added');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['vehicleType', 'driver', 'bookings']);

        return view('vehicles.show', compact('vehicle'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle)
    {
        $vehicleTypes = VehicleType::where('is_active', true)
            ->orWhere('id', $vehicle->vehicle_type_id)
            ->get();
        $drivers = DriverProfile::all();

        return view('vehicles.edit', compact('vehicle', 'vehicleTypes', 'drivers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validatedData = $request->validate([
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'driver_id' => 'required|exists:driver_profiles,id',
            'name' => 'required|string|max:150',
            'plate_number' => 'required|string|max:30|unique:vehicles,plate_number,'.$vehicle->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'capacity_kg' => 'required|string|in:small,medium,large',
            'capacity_m3' => 'required|string|in:small,medium,large',
            'base_price' => 'required|numeric|min:0',
            'price_per_km' => 'required|numeric|min:0',
            'status' => 'required|string|in:active,inactive,maintenance',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $this->storeImage($request->file('image'));
            $this->deleteImage($vehicle->image);
            $validatedData['image'] = $imagePath;
        }

        $vehicle->update($validatedData);

        return redirect()->route('vehicles.index')->with('success', 'vehicle has been updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        $this->deleteImage($vehicle->image);
        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('success', 'vehicle has been deleted');
    }
    
    private function storeImage(UploadedFile $image): string
    {
        $directory = public_path('images/vehicles');
        File::ensureDirectoryExists($directory);

        $imageName = Str::uuid().'.'.$image->extension();
        $image->move($directory, $imageName);

        return 'images/vehicles/'.$imageName;
    }

    private function deleteImage(?string $imagePath): void
    {
        if ($imagePath !== null) {
            $fullPath = public_path($imagePath);
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }
    }
}
