<?php

namespace App\Http\Livewire;

use Session;
use App\Models\User;
use Livewire\Component;
use App\Helpers\RotationHelper;
use Illuminate\Database\Eloquent\Collection;

class UserListTable extends Component
{
    public $selectedRotation;

    public function mount()
    {
        $this->selectedRotation = $this->getSelectedRotation();
    }

    public function render()
    {
        return view('livewire.user-list-table', [
            'rotations' => RotationHelper::getInstance()->getRotations(),
            'users' => $this->getAvailableUsers(),
            'selectedRotation' => $this->selectedRotation
        ]);
    }

    public function updatedSelectedRotation($rotationId)
    {
        if (!hasPermission('users_list_select_rotation')) {
            return;
        }

        Session::put($this->getCacheKeyName(), $rotationId);

        // TODO: fix the table reloading $this->dispatchBrowserEvent('updatedSelectedRotation');
    }

    private function getSelectedRotation(): int
    {
        if (hasPermission('users_list_select_rotation') && Session::has($this->getCacheKeyName())) {
            return Session::get($this->getCacheKeyName());
        }

        $currentRotation = RotationHelper::getInstance()->getCurrentRotation();

        if (!is_null($currentRotation)) {
            return $currentRotation->id;
        }
    }

    private function getCacheKeyName(): string
    {
        return auth()->id() . '-user_list_rotation';
    }

    private function getAvailableUsers(): Collection
    {
        if ($this->selectedRotation == '*') {
            return User::all();
        }

        return User::whereHas('rotations', function ($query) {
            $query->where('rotation_id', $this->selectedRotation);
        })->get();
    }
}
