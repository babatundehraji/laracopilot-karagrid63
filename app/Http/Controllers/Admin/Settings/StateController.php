<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StateController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $query = State::with('country');

            if ($request->filled('country_id')) {
                $query->where('country_id', $request->country_id);
            }

            $states = $query->orderBy('name')->paginate(15);
            $countries = Country::orderBy('name')->get();

            return view('admin.settings.states.index', compact('states', 'countries'));
        } catch (\Exception $e) {
            Log::error('Failed to load states', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to load states: ' . $e->getMessage());
        }
    }

    public function create()
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        $countries = Country::orderBy('name')->get();
        return view('admin.settings.states.create', compact('countries'));
    }

    public function store(Request $request)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'country_id' => 'required|exists:countries,id',
            ]);

            State::create([
                'name' => $request->name,
                'country_id' => $request->country_id,
            ]);

            Log::info('Admin created state', [
                'admin_id' => Auth::guard('web')->id(),
                'state_name' => $request->name
            ]);

            return redirect()->route('admin.settings.states.index')
                ->with('success', 'State created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create state', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to create state: ' . $e->getMessage());
        }
    }

    public function edit(State $state)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        $countries = Country::orderBy('name')->get();
        return view('admin.settings.states.edit', compact('state', 'countries'));
    }

    public function update(Request $request, State $state)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'country_id' => 'required|exists:countries,id',
            ]);

            $state->update([
                'name' => $request->name,
                'country_id' => $request->country_id,
            ]);

            Log::info('Admin updated state', [
                'admin_id' => Auth::guard('web')->id(),
                'state_id' => $state->id
            ]);

            return redirect()->route('admin.settings.states.index')
                ->with('success', 'State updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update state', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to update state: ' . $e->getMessage());
        }
    }

    public function destroy(State $state)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            if ($state->cities()->count() > 0) {
                return back()->with('error', 'Cannot delete state with existing cities');
            }

            $state->delete();

            Log::info('Admin deleted state', [
                'admin_id' => Auth::guard('web')->id(),
                'state_id' => $state->id
            ]);

            return redirect()->route('admin.settings.states.index')
                ->with('success', 'State deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete state', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete state: ' . $e->getMessage());
        }
    }
}