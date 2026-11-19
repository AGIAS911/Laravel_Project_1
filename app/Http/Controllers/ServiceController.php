<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function setStatus(Request $request, Service $service)
    {
        $data = $request->validate(['is_active' => 'required|boolean']);
        abort_if($service->type === Service::TYPE_TRANSPORT && !$request->boolean('is_active'), 422);
        $service->update(['is_active' => $data['is_active']]);

        return back()->with('success', 'Status updated.');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->user()?->role !== 'admin') {
            $services = Service::where('is_active', true)->paginate(12);

            return view('info.services', compact('services'));
        }
        $services = Service::latest()->paginate(10);
        return view('services.index',compact('services'));
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
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|string|max:255',
            'unit'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'is_active'   => 'boolean',
        ]);
        $validated['is_active'] = $request->has('is_active');

        Service::create($validated);

        return redirect()->route('services.index')
        ->with('success', 'The service has been created successfully.');   
         }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
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
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|string|max:255',
            'unit'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'is_active'   => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $service->update($validated);

        return redirect()->route('services.index')
                         ->with('success', '  Updated Successfully .');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('services.index')
        ->with('success','deleted successfully!');

    }
}
