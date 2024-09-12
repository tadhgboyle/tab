<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Models\FamilyMember;
use Barryvdh\DomPDF\Facade\Pdf;

class FamilyMemberController extends Controller
{
    public function show(FamilyMember $familyMember)
    {
        return view('pages.user.family.members.view', [
            'familyMember' => $familyMember,
            'user' => $familyMember->user,
        ]);
    }

    public function downloadPdf(FamilyMember $familyMember)
    {
        $user = $familyMember->user;

        $timestamp = now()->timestamp;

        // TODO "common" directory needed?
        return Pdf::loadView('pdfs.admin.user', [
            'user' => $user,
        ])->stream("user-{$user->id}-{$timestamp}.pdf");
    }
}