<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Models\FamilyMembership;
use Barryvdh\DomPDF\Facade\Pdf;

class FamilyMembershipController extends Controller
{
    public function show(FamilyMembership $familyMembership)
    {
        return view('pages.user.family.memberships.view', [
            'familyMembership' => $familyMembership,
            'user' => $familyMembership->user,
        ]);
    }

    public function downloadPdf(FamilyMembership $familyMembership)
    {
        $user = $familyMembership->user;

        $timestamp = now()->timestamp;

        // TODO "common" directory needed?
        return Pdf::loadView('pdfs.admin.user', [
            'user' => $user,
        ])->stream("user-{$user->id}-{$timestamp}.pdf");
    }
}