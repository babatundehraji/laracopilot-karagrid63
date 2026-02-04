<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CityController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $query = City::with('state.country');

            if ($request->filled('state_id')) {
                $query->where('state_id', $request->state_id);
            }

            $cities = $query->orderBy('name')->paginate(15);
            $states = State::with('country')->orderBy('name')->get();

            return view('admin.settings.cities.index', compact('cities', 'states'));
        } catch (\Exception $e) {
            Log::error('Failed to load cities', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to load cities: ' . $e->getMessage());
        }
    }

    public function create()
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        $states = State::with('country')->orderBy('name')->get();
        return view('admin.settings.cities.create', compact('states'));
    }

    public function store(Request $request)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'state_id' => 'required|exists:states,id',
            ]);

            City::create([
                'name' => $request->name,
                'state_id' => $request->state_id,
            ]);

            Log::info('Admin created city', [
                'admin_id' => Auth::guard('web')->id(),
                'city_name' => $request->name
            ]);

            return redirect()->route('admin.settings.cities.index')
                ->with('success', 'City created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create city', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to create city: ' . $e->getMessage());
        }
    }

    public function edit(City $city)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        $states = State::with('country')->orderBy('name')->get();
        return view('admin.settings.cities.edit', compact('city', 'states'));
    }

    public function update(Request $request, City $city)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'state_id' => 'required|exists:states,id',
            ]);

            $city->update([
                'name' => $request->name,
                'state_id' => $request->state_id,
            ]);

            Log::info('Admin updated city', [
                'admin_id' => Auth::guard('web')->id(),
                'city_id' => $city->id
            ]);

            return redirect()->route('admin.settings.cities.index')
                ->with('success', 'City updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update city', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to update city: ' . $e->getMessage());
        }
    }

    public function destroy(City $city)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $city->delete();

            Log::info('Admin deleted city', [
                'admin_id' => Auth::guard('web')->id(),
                'city_id' => $city->id
            ]);

            return redirect()->route('admin.settings.cities.index')
                ->with('success', 'City deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete city', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete city: ' . $e->getMessage());
        }
    }
}