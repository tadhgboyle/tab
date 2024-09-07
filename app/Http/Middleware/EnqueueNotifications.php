<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Filament\Notifications\Notification;

class EnqueueNotifications
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $permission
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // TODO some controllers return with singular `error`, whereas Validator returns with plural `errors`
        $validatorErrors = $request->session()->get('errors', collect());
        $error = $request->session()->get('error');
        $errors = $validatorErrors->merge($error ? [$error] : []);

        foreach ($errors->all() as $error) {
            Notification::make()
                ->title('Error')
                ->body($error)
                ->danger()
                ->send();
        }

        $success = $request->session()->get('success');
        if ($success) {
            Notification::make()
                ->title('Success')
                ->body($success)
                ->success()
                ->send();
        }

        return $next($request);
    }
}
