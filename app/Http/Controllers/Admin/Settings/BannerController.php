<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $banners = Banner::with(['category', 'subcategory'])
                ->orderBy('order')
                ->paginate(15);
            return view('admin.settings.banners.index', compact('banners'));
        } catch (\Exception $e) {
            Log::error('Failed to load banners', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to load banners: ' . $e->getMessage());
        }
    }

    public function create()
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        $categories = Category::orderBy('name')->get();
        $subcategories = Subcategory::orderBy('name')->get();
        return view('admin.settings.banners.create', compact('categories', 'subcategories'));
    }

    public function store(Request $request)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'subtitle' => 'nullable|string|max:255',
                'button_text' => 'nullable|string|max:100',
                'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
                'category_id' => 'nullable|exists:categories,id',
                'subcategory_id' => 'nullable|exists:subcategories,id',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0|gte:min_price',
                'order' => 'nullable|integer|min:0',
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('banners', 'public');
            }

            Banner::create([
                'title' => $request->title,
                'subtitle' => $request->subtitle,
                'button_text' => $request->button_text,
                'image_url' => $imagePath,
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'min_price' => $request->min_price,
                'max_price' => $request->max_price,
                'order' => $request->order ?? 0,
                'is_active' => true,
            ]);

            Log::info('Admin created banner', [
                'admin_id' => Auth::guard('web')->id(),
                'banner_title' => $request->title
            ]);

            return redirect()->route('admin.settings.banners.index')
                ->with('success', 'Banner created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create banner', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to create banner: ' . $e->getMessage());
        }
    }

    public function edit(Banner $banner)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        $categories = Category::orderBy('name')->get();
        $subcategories = Subcategory::orderBy('name')->get();
        return view('admin.settings.banners.edit', compact('banner', 'categories', 'subcategories'));
    }

    public function update(Request $request, Banner $banner)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'subtitle' => 'nullable|string|max:255',
                'button_text' => 'nullable|string|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'category_id' => 'nullable|exists:categories,id',
                'subcategory_id' => 'nullable|exists:subcategories,id',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0|gte:min_price',
                'order' => 'nullable|integer|min:0',
                'is_active' => 'boolean',
            ]);

            $data = [
                'title' => $request->title,
                'subtitle' => $request->subtitle,
                'button_text' => $request->button_text,
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'min_price' => $request->min_price,
                'max_price' => $request->max_price,
                'order' => $request->order ?? 0,
                'is_active' => $request->has('is_active'),
            ];

            if ($request->hasFile('image')) {
                // Delete old image
                if ($banner->image_url) {
                    Storage::disk('public')->delete($banner->image_url);
                }
                $data['image_url'] = $request->file('image')->store('banners', 'public');
            }

            $banner->update($data);

            Log::info('Admin updated banner', [
                'admin_id' => Auth::guard('web')->id(),
                'banner_id' => $banner->id
            ]);

            return redirect()->route('admin.settings.banners.index')
                ->with('success', 'Banner updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update banner', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to update banner: ' . $e->getMessage());
        }
    }

    public function destroy(Banner $banner)
    {
        if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'admin') {
            return redirect()->route('admin.login');
        }

        try {
            // Delete image
            if ($banner->image_url) {
                Storage::disk('public')->delete($banner->image_url);
            }

            $banner->delete();

            Log::info('Admin deleted banner', [
                'admin_id' => Auth::guard('web')->id(),
                'banner_id' => $banner->id
            ]);

            return redirect()->route('admin.settings.banners.index')
                ->with('success', 'Banner deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete banner', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete banner: ' . $e->getMessage());
        }
    }
}