<?php

namespace App\Http\Controllers;

use App\Models\Rotation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use App\Helpers\RotationHelper;
use App\Http\Requests\RotationRequest;

class RotationController extends Controller
{
    public function create()
    {
        return view('pages.settings.rotations.form', [
            'start' => Carbon::now(),
            'end' => Carbon::now()->addWeek(),
        ]);
    }

    public function store(RotationRequest $request): RedirectResponse
    {
        $rotation = new Rotation();
        $rotation->name = $request->name;
        $rotation->start = $request->start;
        $rotation->end = $request->end;
        $rotation->save();

        return redirect()->route('settings')->with('success', "Created new rotation {$rotation->name}.");
    }

    public function edit(Rotation $rotation)
    {
        return view('pages.settings.rotations.form', [
            'rotation' => $rotation,
            'start' => $rotation->start,
            'end' => $rotation->end,
        ]);
    }

    public function update(RotationRequest $request, Rotation $rotation): RedirectResponse
    {
        $rotation->update([
            'name' => $request->name,
            'start' => $request->start,
            'end' => $request->end,
        ]);

        return redirect()->route('settings')->with('success', "Updated rotation {$rotation->name}.");
    }

    // TODO: fallback category logic similar to roles
    public function delete(Rotation $rotation): RedirectResponse
    {
        $rotation->delete();

        return redirect()->route('settings')->with('success', "Deleted rotation {$rotation->name}.");
    }
}
