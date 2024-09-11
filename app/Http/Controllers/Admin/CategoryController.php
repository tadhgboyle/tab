<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\CategoryRequest;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    public function create()
    {
        return view('pages.admin.settings.categories.form');
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = new Category();
        $category->name = $request->name;
        $category->type = $request->type;
        $category->save();

        return redirect()->route('settings')->with('success', "Created category $category->name.");
    }

    public function edit(Category $category)
    {
        return view('pages.admin.settings.categories.form', [
            'category' => $category,
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update([
            'name' => $request->name,
            'type' => $request->type
        ]);

        return redirect()->route('settings')->with('success', "Updated category $category->name.");
    }

    public function delete(Category $category): RedirectResponse
    {
        // TODO: Tests and frontend validation for this
        if ($category->products->count() > 0) {
            return redirect()->back()->with('error', "Cannot delete category $category->name because it has products.");
        }

        $category->delete();

        return redirect()->route('settings')->with('success', "Deleted category $category->name.");
    }
}
