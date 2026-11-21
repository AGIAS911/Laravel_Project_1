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
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->role === 'driver' && $user->driverProfile?->status !== 'approved') {
            return redirect()->route($user->driverProfile ? 'driver.dashboard' : 'driver.profile.edit');
        }

        $driver = $this->managingDriver($request);
        $status = $request->query('status');
        $status = in_array($status, ['active', 'maintenance', 'inactive'], true) ? $status : null;
        $search = $request->query('q');
        $search = is_string($search) ? trim(substr($search, 0, 100)) : '';
        $vehicles = Vehicle::with(['vehicleType', 'driver.user'])
            ->when($driver, fn ($query) => $query->where('driver_id', $driver->id))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search !== '', fn ($query) => $query->where(fn ($rows) => $rows
                ->where('name', 'like', '%'.$search.'%')->orWhere('plate_number', 'like', '%'.$search.'%')))
            ->withCount('bookings')
            ->latest()
            ->paginate(10)->withQueryString();

        $view = $driver ? 'vehicles.driver-index' : 'vehicles.index';

        return view($view, compact('vehicles', 'status', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vehicleTypes = VehicleType::where('is_active', true)->get();
        $driver = $this->managingDriver(request());
        if ($driver) {
            return view('vehicles.driver-form', ['vehicle' => null, 'vehicleTypes' => $vehicleTypes]);
        }

        $drivers = DriverProfile::all();

        return view('vehicles.create', compact('vehicleTypes', 'drivers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $driver = $this->managingDriver($request);
        if ($driver) {
            $request->merge(['driver_id' => $driver->id]);
        }
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

        return redirect()->route('vehicles.index')->with('success', 'Vehicle has been added.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        $this->managingDriver(request(), $vehicle);
        $vehicle->load(['vehicleType', 'driver.user', 'bookings']);

        return view('vehicles.show', compact('vehicle'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle)
    {
        $driver = $this->managingDriver(request(), $vehicle);
        $vehicleTypes = VehicleType::where('is_active', true)
            ->orWhere('id', $vehicle->vehicle_type_id)
            ->get();
        if ($driver) {
            return view('vehicles.driver-form', compact('vehicle', 'vehicleTypes'));
        }

        $drivers = DriverProfile::all();

        return view('vehicles.edit', compact('vehicle', 'vehicleTypes', 'drivers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $driver = $this->managingDriver($request, $vehicle);
        if ($driver) {
            $request->merge(['driver_id' => $driver->id]);
        }
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

        if ((int) $validatedData['driver_id'] !== $vehicle->driver_id || $validatedData['status'] !== 'active') {
            abort_if($vehicle->bookings()->whereIn('status', ['pending', 'awaiting_deposit', 'confirmed', 'in_progress'])->exists(),
                409, 'Resolve active bookings before reassigning or deactivating this vehicle.');
        }

        if ($request->hasFile('image')) {
            $imagePath = $this->storeImage($request->file('image'));
            $this->deleteImage($vehicle->image);
            $validatedData['image'] = $imagePath;
        }

        $vehicle->update($validatedData);

        return redirect()->route('vehicles.index')->with('success', 'Vehicle has been updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        $this->managingDriver(request(), $vehicle);
        if ($vehicle->bookings()->exists()) {
            return redirect()->route('vehicles.index')
                ->with('error', 'A vehicle linked to bookings cannot be deleted.');
        }

        $this->deleteImage($vehicle->image);
        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('success', 'Vehicle has been deleted.');
    }
    
    private function managingDriver(Request $request, ?Vehicle $vehicle = null): ?DriverProfile
    {
        if ($request->user()->role === 'admin') {
            return null;
        }
        $driver = $request->user()->driverProfile;
        abort_unless($request->user()->role === 'driver' && $driver?->status === 'approved', 403);
        abort_if($vehicle && $vehicle->driver_id !== $driver->id, 404);

        return $driver;
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
