<?php

namespace App\Helpers;

use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class NotificationHelper
{
    public function sendSuccessNotification(string $title, string $body, array $actions = []): void
    {
        Notification::make()
            ->title($title)
            ->body($body)
            ->success()
            ->actions($this->buildActions($actions))
            ->send();
    }

    private function buildActions(array $actions): array
    {
        $return = [];

        foreach ($actions as $action) {
            $return[] = Action::make($action['name'])
                ->url($action['url']);
        }

        return $return;
    }
}
