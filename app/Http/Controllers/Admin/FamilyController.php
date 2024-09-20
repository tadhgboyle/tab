<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\FamilyRequest;
use App\Models\Family;
use Barryvdh\DomPDF\Facade\Pdf;

class FamilyController extends Controller
{
    public function index()
    {
        return view('pages.admin.families.list');
    }

    public function show(Family $family)
    {
        return view('pages.admin.families.view', [
            'family' => $family,
        ]);
    }

    public function create()
    {
        return view('pages.admin.families.form');
    }

    public function store(FamilyRequest $request)
    {
        $family = Family::create($request->validated());

        return redirect()->route('families_view', $family)->with('success', "{$family->name} family created.");
    }

    public function edit(Family $family)
    {
        return view('pages.admin.families.form', [
            'family' => $family,
        ]);
    }

    public function update(FamilyRequest $request, Family $family)
    {
        $family->update($request->validated());

        return redirect()->route('families_view', $family)->with('success', 'Family updated.');
    }

    public function downloadPdf(Family $family)
    {
        $timestamp = now()->timestamp;

        return Pdf::loadView('pdfs.user.family', [
            'family' => $family,
        ])->stream("family-{$family->id}-{$timestamp}.pdf");
    }
}