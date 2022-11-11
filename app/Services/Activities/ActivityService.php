<?php

namespace App\Services\Activities;

use App\Models\Activity;

trait ActivityService
{
    protected Activity $_activity;

    final public function getActivity(): Activity
    {
        return $this->_activity;
    }
}
