<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class CashierController extends Controller
{
    public function __invoke()
    {
        // TODO: similar handling of rotation selection/invalidity to statistics page
        return view('pages.admin.cashier');
    }
}
