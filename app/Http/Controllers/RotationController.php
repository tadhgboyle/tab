<?php

namespace App\Http\Controllers;

use App\Helpers\RotationHelper;
use App\Http\Requests\RotationRequest;
use App\Models\Rotation;

class RotationController extends Controller
{
    public function form()
    {
        return view('pages.settings.rotations.form', [
            'rotation' => Rotation::find(request()->route('id')),
        ]);
    }

    public function new(RotationRequest $request)
    {
        if (RotationHelper::getInstance()->doesRotationOverlap($request->start, $request->end)) {
            //
            return;
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
        if (RotationHelper::getInstance()->doesRotationOverlap($request->start, $request->end)) {
            // TODO: Can move this into rotationrequest class?
            return;
        }

        $rotation = Category::find($request->rotation_id);

        $rotation->update([
            'name' => $request->name,
            'start' => $request->start,
            'end' => $request->end,
        ]);

        return redirect()->route('settings')->with('success', "Updated rotation {$request->name}.");
    }

    // TODO: fallback category logic similar to roles
    public function delete(int $rotation_id)
    {
        $rotation = Rotation::find($rotation_id);

        $rotation->delete();

        return redirect()->route('settings')->with('success', "Deleted rotation {$rotation->name}.");
    }
}
