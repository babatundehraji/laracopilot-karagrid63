<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CountryController extends Controller
{
    public function index()
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $countries = Country::orderBy('name')->paginate(15);
            return view('admin.settings.countries.index', compact('countries'));
        } catch (\Exception $e) {
            Log::error('Failed to load countries', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to load countries: ' . $e->getMessage());
        }
    }

    public function create()
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        return view('admin.settings.countries.create');
    }

    public function store(Request $request)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:countries,name',
                'code' => 'required|string|size:2|unique:countries,code',
            ]);

            Country::create([
                'name' => $request->name,
                'code' => strtoupper($request->code),
            ]);

            Log::info('Admin created country', [
                'admin_id' => Auth::guard('web')->id(),
                'country_name' => $request->name
            ]);

            return redirect()->route('admin.settings.countries.index')
                ->with('success', 'Country created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create country', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to create country: ' . $e->getMessage());
        }
    }

    public function edit(Country $country)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        return view('admin.settings.countries.edit', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:countries,name,' . $country->id,
                'code' => 'required|string|size:2|unique:countries,code,' . $country->id,
            ]);

            $country->update([
                'name' => $request->name,
                'code' => strtoupper($request->code),
            ]);

            Log::info('Admin updated country', [
                'admin_id' => Auth::guard('web')->id(),
                'country_id' => $country->id
            ]);

            return redirect()->route('admin.settings.countries.index')
                ->with('success', 'Country updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update country', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to update country: ' . $e->getMessage());
        }
    }

    public function destroy(Country $country)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            // Check if country has states
            if ($country->states()->count() > 0) {
                return back()->with('error', 'Cannot delete country with existing states');
            }

            $country->delete();

            Log::info('Admin deleted country', [
                'admin_id' => Auth::guard('web')->id(),
                'country_id' => $country->id
            ]);

            return redirect()->route('admin.settings.countries.index')
                ->with('success', 'Country deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete country', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete country: ' . $e->getMessage());
        }
    }
}