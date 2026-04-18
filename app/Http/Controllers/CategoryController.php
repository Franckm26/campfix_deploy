<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();

        return view('admin.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
        ]);

        Category::create([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Category added');
    }

    public function edit(Category $category)
    {
        $categories = Category::all();

        return view('admin.categories', compact('categories', 'category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|unique:categories,name,'.$category->id,
        ]);

        $category->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.categories')->with('success', 'Category updated successfully');
    }

    public function destroy(Category $category)
    {
        // Check if category has concerns
        if ($category->concerns()->count() > 0) {
            return back()->with('error', 'Cannot delete category with existing concerns');
        }

        $category->delete();

        return back()->with('success', 'Category deleted successfully');
    }

    // ============ API METHODS ============

    /**
     * API: List categories
     */
    public function apiIndex()
    {
        return response()->json(['categories' => Category::all()]);
    }

    /**
     * API: Create category (admin)
     */
    public function apiStore(Request $request)
    {
        $user = $request->user();
        if (! in_array($user->role, ['mis', 'school_admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate(['name' => 'required|unique:categories']);
        $category = Category::create(['name' => $request->name]);

        return response()->json(['category' => $category], 201);
    }

    /**
     * API: Delete category (admin)
     */
    public function apiDestroy(Request $request, Category $category)
    {
        $user = $request->user();
        if (! in_array($user->role, ['mis', 'school_admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($category->concerns()->count() > 0) {
            return response()->json(['error' => 'Category in use'], 400);
        }

        $category->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
