<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FamilyMemberRole;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\User;

class FamilyMemberController
{
    public function store(Family $family, User $user)
    {
        if ($user->family) {
            return redirect()->back()->with('error', "$user->full_name is already in a family.");
        }

        $family->members()->create([
            'user_id' => $user->id,
            'role' => FamilyMemberRole::Member,
        ]);

        return redirect()->back()->with('success', "$user->full_name added to $family->name.");
    }

    public function update(Family $family, FamilyMember $familyMember)
    {
        $familyMember->update([
            'role' => request('role'),
        ]);

        return redirect()->back()->with('success', "{$familyMember->user->full_name} role updated to " . ucfirst($familyMember->role->value) . ".");
    }

    public function delete(Family $family, FamilyMember $familyMember)
    {
        $familyMember->delete();

        return redirect()->back()->with('success', "{$familyMember->user->full_name} removed from {$family->name}.");
    }

    // TODO: livewire
    public function ajaxUserSearch(Family $family): string
    {
        $users = User::query()
                        ->where('full_name', 'LIKE', '%' . request('search') . '%')
                        ->limit(7)
                        ->get()
                        ->all();
        $output = '';

        foreach ($users as $user) {
            $output .=
                '<tr>' .
                    '<td>' . $user->full_name . '</td>' .
                    ($user->family
                        ? '<td><button class="button is-success is-small" disabled>Add</button></td>'
                        : '<td><a href="' . route('families_user_add', [$family->id, $user->id]) . '" class="button is-success is-small">Add</a></td>') .
                '</tr>';
        }

        return $output;
    }
}