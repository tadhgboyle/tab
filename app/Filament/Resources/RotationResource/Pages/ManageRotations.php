<?php

namespace App\Filament\Resources\RotationResource\Pages;

use App\Filament\Resources\RotationResource;
use App\Helpers\RotationHelper;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageRotations extends ManageRecords
{
    protected static string $resource = RotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
