<?php

namespace App\Http\Controllers;

use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class VehicleTypeController extends Controller
{
    public function index()
    {
        $vehicleTypes = VehicleType::withCount('vehicles')->latest()->paginate(10);

        return view('vehicle-types.index', compact('vehicleTypes'));
    }

    public function create()
    {
        return view('vehicle-types.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate($this->validationRules());
        $validatedData['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $validatedData['image'] = $this->storeImage($request->file('image'));
        }

        VehicleType::create($validatedData);

        return redirect()->route('vehicle-types.index')->with('success', 'Vehicle type has been added.');
    }

    public function show(VehicleType $vehicleType)
    {
        $vehicleType->loadCount('vehicles');

        return view('vehicle-types.show', compact('vehicleType'));
    }

    public function edit(VehicleType $vehicleType)
    {
        return view('vehicle-types.edit', compact('vehicleType'));
    }

    public function update(Request $request, VehicleType $vehicleType)
    {
        $validatedData = $request->validate($this->validationRules($vehicleType));
        $validatedData['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $imagePath = $this->storeImage($request->file('image'));
            $this->deleteImage($vehicleType->image);
            $validatedData['image'] = $imagePath;
        }

        $vehicleType->update($validatedData);

        return redirect()->route('vehicle-types.index')->with('success', 'Vehicle type has been updated.');
    }

    public function destroy(VehicleType $vehicleType)
    {
        if ($vehicleType->vehicles()->exists()) {
            return redirect()->route('vehicle-types.index')
                ->with('error', 'A vehicle type linked to vehicles cannot be deleted.');
        }

        $this->deleteImage($vehicleType->image);
        $vehicleType->delete();

        return redirect()->route('vehicle-types.index')->with('success', 'Vehicle type has been deleted.');
    }

    private function validationRules(?VehicleType $vehicleType = null): array
    {
        $uniqueName = 'unique:vehicle_types,name';
        if ($vehicleType !== null) {
            $uniqueName .= ','.$vehicleType->id;
        }

        return [
            'name' => ['required', 'string', 'max:100', $uniqueName],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    private function storeImage(UploadedFile $image): string
    {
        $directory = public_path('images/vehicle-types');
        File::ensureDirectoryExists($directory);

        $imageName = Str::uuid().'.'.$image->extension();
        $image->move($directory, $imageName);

        return 'images/vehicle-types/'.$imageName;
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
