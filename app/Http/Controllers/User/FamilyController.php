<?php

namespace App\Http\Controllers\User;

use App\Models\Family;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;

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

        return Pdf::loadView('pdfs.user.family', [
            'family' => $family,
        ])->stream("family-{$family->id}-{$timestamp}.pdf");
    }
}
