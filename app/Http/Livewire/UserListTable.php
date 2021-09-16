<?php

namespace App\Http\Livewire;

use App\Helpers\RotationHelper;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

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

        Cache::forever($this->getCacheKeyName(), $rotationId);
    }

    private function getSelectedRotation()
    {
        if (hasPermission('users_list_select_rotation') && Cache::has($this->getCacheKeyName())) {
            return Cache::get($this->getCacheKeyName());
        }

        $currentRotation = RotationHelper::getInstance()->getCurrentRotation();

        if (!is_null($currentRotation)) {
            return $currentRotation->id;
        }
    }

    private function getCacheKeyName()
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
