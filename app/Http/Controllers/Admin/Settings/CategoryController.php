<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $categories = Category::withCount('subcategories')->orderBy('name')->paginate(15);
            return view('admin.settings.categories.index', compact('categories'));
        } catch (\Exception $e) {
            Log::error('Failed to load categories', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to load categories: ' . $e->getMessage());
        }
    }

    public function create()
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        return view('admin.settings.categories.create');
    }

    public function store(Request $request)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string',
            ]);

            Category::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
            ]);

            Log::info('Admin created category', [
                'admin_id' => Auth::guard('web')->id(),
                'category_name' => $request->name
            ]);

            return redirect()->route('admin.settings.categories.index')
                ->with('success', 'Category created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create category', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to create category: ' . $e->getMessage());
        }
    }

    public function edit(Category $category)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        return view('admin.settings.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
                'description' => 'nullable|string',
            ]);

            $category->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
            ]);

            Log::info('Admin updated category', [
                'admin_id' => Auth::guard('web')->id(),
                'category_id' => $category->id
            ]);

            return redirect()->route('admin.settings.categories.index')
                ->with('success', 'Category updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update category', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to update category: ' . $e->getMessage());
        }
    }

    public function destroy(Category $category)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            if ($category->subcategories()->count() > 0) {
                return back()->with('error', 'Cannot delete category with existing subcategories');
            }

            if ($category->services()->count() > 0) {
                return back()->with('error', 'Cannot delete category with existing services');
            }

            $category->delete();

            Log::info('Admin deleted category', [
                'admin_id' => Auth::guard('web')->id(),
                'category_id' => $category->id
            ]);

            return redirect()->route('admin.settings.categories.index')
                ->with('success', 'Category deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete category', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete category: ' . $e->getMessage());
        }
    }
}