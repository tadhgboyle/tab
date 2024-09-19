<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Models\Family;
use Barryvdh\DomPDF\Facade\Pdf;

class FamilyController extends Controller
{
    public function show(Family $family)
    {
        return view('pages.user.family.view', [
            'family' => $family,
        ]);
    }

    public function downloadPdf(Family $family)
    {
        $timestamp = now()->timestamp;

        // TODO "common" directory needed?
        return Pdf::loadView('pdfs.user.family', [
            'family' => $family,
        ])->stream("family-{$family->id}-{$timestamp}.pdf");
    }
}