<?php

namespace App\Services;

use Illuminate\Http\RedirectResponse;

abstract class HttpService extends Service
{
    abstract public function redirect(): RedirectResponse;
}
