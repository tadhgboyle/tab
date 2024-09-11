<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;

class FamilyController extends Controller
{
    public function show()
    {
        return view('pages.user.family.view', [
            'family' => auth()->user()->family,
        ]);
    }

    public function downloadPdf()
    {
        $family = auth()->user()->family;

        $timestamp = now()->timestamp;

        // TODO "common" directory needed?
        return Pdf::loadView('pdfs.user.family', [
            'family' => $family,
        ])->stream("family-{$family->id}-{$timestamp}.pdf");
    }
}