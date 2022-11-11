<?php

namespace App\Services\Activities;

use App\Models\ActivityRegistration;

trait ActivityRegistrationService
{
    protected ActivityRegistration $_activity_registration;

    final public function getActivityRegistration(): ActivityRegistration
    {
        return $this->_activity_registration;
    }
}
