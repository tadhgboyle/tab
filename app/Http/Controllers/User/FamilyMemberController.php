<?php

namespace App\Http\Controllers\User;
use App\Helpers\CategoryHelper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Services\Users\UserLimits\UserLimitUpsertService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

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

    public function update(Request $request, Family $family, FamilyMember $familyMember)
    {
        if (auth()->user()->familyMember->is($familyMember) && request('role') !== $familyMember->role->value) {
            return redirect()->route('families_member_view', [$family, $familyMember])->with('error', 'You cannot update your own family role.');
        }

        $userLimitsUpsertService = new UserLimitUpsertService($familyMember->user, $request);
        if ($userLimitsUpsertService->getResult() === UserLimitUpsertService::RESULT_NEGATIVE_LIMIT) {
            return redirect()->back()->with('error', 'Limit cannot be negative.');
        }

        $familyMember->update([
            'role' => request('role'),
        ]);

        return redirect()->route('families_member_view', [$family, $familyMember])->with('success', 'Family member updated.');
    }

    public function downloadPdf(Family $family, FamilyMember $familyMember)
    {
        $user = $familyMember->user;

        $timestamp = now()->timestamp;

        return Pdf::loadView('pdfs.common.user', [
            'user' => $user,
        ])->stream("user-{$user->id}-{$timestamp}.pdf");
    }
}