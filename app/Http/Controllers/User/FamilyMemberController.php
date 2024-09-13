<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyMember;
use Barryvdh\DomPDF\Facade\Pdf;

class FamilyMemberController extends Controller
{
    public function show(Family $family, FamilyMember $familyMember)
    {
        return view('pages.user.family.members.view', [
            'familyMember' => $familyMember,
            'user' => $familyMember->user,
        ]);
    }

    public function downloadPdf(Family $family, FamilyMember $familyMember)
    {
        $user = $familyMember->user;

        $timestamp = now()->timestamp;

        // TODO "common" directory needed?
        return Pdf::loadView('pdfs.admin.user', [
            'user' => $user,
        ])->stream("family-{$family->id}-member-{$user->id}-{$timestamp}.pdf");
    }
}