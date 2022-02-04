<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Rotation;
use App\Helpers\RotationHelper;
use App\Http\Requests\RotationRequest;

class RotationController extends Controller
{
    public function form()
    {
        return view('pages.settings.rotations.form', [
            'rotation' => Rotation::find(request()->route('id')),
        ]);
    }

    public function new(RotationRequest $request, RotationHelper $rotationHelper)
    {
        if ($rotationHelper->doesRotationOverlap($request->start, $request->end)) {
            return redirect()->back()->withInput()->with('error', 'That Rotation would overlap an existing Rotation.');
        }

        $rotation = new Rotation();
        $rotation->name = $request->name;
        $rotation->start = $request->start;
        $rotation->end = $request->end;
        $rotation->save();

        return redirect()->route('settings')->with('success', "Created new rotation {$request->name}.");
    }

    public function edit(RotationRequest $request)
    {
        $rotation = Category::find($request->rotation_id);

        $rotation->update([
            'name' => $request->name,
            'start' => $request->start,
            'end' => $request->end,
        ]);

        return redirect()->route('settings')->with('success', "Updated rotation {$request->name}.");
    }

    // TODO: fallback category logic similar to roles
    public function delete(Rotation $rotation)
    {
        $rotation->delete();

        return redirect()->route('settings')->with('success', "Deleted rotation {$rotation->name}.");
    }
}
