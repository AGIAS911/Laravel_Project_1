<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user?->role === 'admin';

        if ($user === null) {
            $services = Service::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->paginate(12);

            return view('info.services', compact('services'));
        }

        $services = Service::query()
            ->when(!$isAdmin, fn ($query) => $query->where('is_active', true))
            ->latest()
            ->paginate($isAdmin ? 10 : 12);

        return view('services.index', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('services.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'type' => 'required|string|max:20',
            'unit' => 'required|string|max:20',
            'price' => 'required|numeric|decimal:0,3|min:0|max:9999999.999',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        Service::create($validated);

        return redirect()->route('services.index')
            ->with('success', 'The service has been created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        abort_unless($service->is_active || request()->user()?->role === 'admin', 404);

        return view('services.show', compact('service'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'type' => 'required|string|max:20',
            'unit' => 'required|string|max:20',
            'price' => 'required|numeric|decimal:0,3|min:0|max:9999999.999',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $service->update($validated);

        return redirect()->route('services.index')
            ->with('success', 'The service has been updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'The service has been deleted successfully.');
    }
}
