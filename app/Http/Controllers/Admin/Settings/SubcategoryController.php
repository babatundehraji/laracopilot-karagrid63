<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $query = Subcategory::with('category');

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            $subcategories = $query->orderBy('name')->paginate(15);
            $categories = Category::orderBy('name')->get();

            return view('admin.settings.subcategories.index', compact('subcategories', 'categories'));
        } catch (\Exception $e) {
            Log::error('Failed to load subcategories', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to load subcategories: ' . $e->getMessage());
        }
    }

    public function create()
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        $categories = Category::orderBy('name')->get();
        return view('admin.settings.subcategories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'description' => 'nullable|string',
            ]);

            Subcategory::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'category_id' => $request->category_id,
                'description' => $request->description,
            ]);

            Log::info('Admin created subcategory', [
                'admin_id' => Auth::guard('web')->id(),
                'subcategory_name' => $request->name
            ]);

            return redirect()->route('admin.settings.subcategories.index')
                ->with('success', 'Subcategory created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create subcategory', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to create subcategory: ' . $e->getMessage());
        }
    }

    public function edit(Subcategory $subcategory)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        $categories = Category::orderBy('name')->get();
        return view('admin.settings.subcategories.edit', compact('subcategory', 'categories'));
    }

    public function update(Request $request, Subcategory $subcategory)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'description' => 'nullable|string',
            ]);

            $subcategory->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'category_id' => $request->category_id,
                'description' => $request->description,
            ]);

            Log::info('Admin updated subcategory', [
                'admin_id' => Auth::guard('web')->id(),
                'subcategory_id' => $subcategory->id
            ]);

            return redirect()->route('admin.settings.subcategories.index')
                ->with('success', 'Subcategory updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update subcategory', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to update subcategory: ' . $e->getMessage());
        }
    }

    public function destroy(Subcategory $subcategory)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            if ($subcategory->services()->count() > 0) {
                return back()->with('error', 'Cannot delete subcategory with existing services');
            }

            $subcategory->delete();

            Log::info('Admin deleted subcategory', [
                'admin_id' => Auth::guard('web')->id(),
                'subcategory_id' => $subcategory->id
            ]);

            return redirect()->route('admin.settings.subcategories.index')
                ->with('success', 'Subcategory deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete subcategory', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete subcategory: ' . $e->getMessage());
        }
    }
}