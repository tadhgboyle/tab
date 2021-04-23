<?php

namespace App\Http\Controllers;

use App\Category;
use App\Http\Requests\CategoryRequest;

// TODO: fallback category logic similar to roles
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

        return redirect()->route('settings')->with('success', 'Created new category ' . $request->name . '.');
    }

    public function edit(CategoryRequest $request)
    {
        $category = Category::find($request->category_id);

        $category->update(['name' => $request->name, 'type' => $request->type]);

        return redirect()->route('settings')->with('success', 'Updated category ' . $request->name . '.');
    }

    public function delete(CategoryRequest $request)
    {
        $category = Category::find($request->category_id);

        $category->update(['deleted' => true]);

        return redirect()->route('settings')->with('success', 'Deleted category ' . $request->name . '.');
    }
}
