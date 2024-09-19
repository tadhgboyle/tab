<?php

namespace App\Http\Controllers\User;
use App\Helpers\CategoryHelper;
use App\Http\Controllers\Controller;
use App\Models\Category;
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

    public function edit(Family $family, FamilyMember $familyMember, CategoryHelper $categoryHelper)
    {
        $user = $familyMember->user;

        $categories = $categoryHelper->getCategories()->map(function (Category $category) use ($user) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'limit' => $user->limitFor($category),
            ];
        });

        return view('pages.user.family.members.edit', [
            'familyMember' => $familyMember,
            'user' => $user,
            'categories' => $categories,
        ]);
    }

    public function update(Family $family, FamilyMember $familyMember)
    {
        $familyMember->update(request()->only('role'));

        return redirect()->route('families_member_view', [$family, $familyMember])->with('success', 'Family member updated.');
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