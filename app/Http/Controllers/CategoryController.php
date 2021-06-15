<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\CategoryRequest;

class CategoryController extends Controller
{
    public function form()
    {
        $category = Category::find(request()->route('id'));

        return view('pages.settings.categories.form', [
            'category' => $category,
        ]);
    }

    public function new(CategoryRequest $request)
    {
        $category = new Category();
        $category->name = $request->name;
        $category->type = $request->type;
        $category->save();

        return redirect()->route('settings')->with('success', "Created new category {$request->name}.");
    }

    public function edit(CategoryRequest $request)
    {
        $category = Category::find($request->category_id);

        $category->update([
            'name' => $request->name,
            'type' => $request->type
        ]);

        return redirect()->route('settings')->with('success', "Updated category {$request->name}.");
    }

    // TODO: fallback category logic similar to roles
    public function delete(int $category_id)
    {
        $category = Category::find($category_id);

        $category->update(['deleted' => true]);

        return redirect()->route('settings')->with('success', "Deleted category {$category->name}.");
    }
}
