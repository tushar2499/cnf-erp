<?php

namespace App\Http\Controllers\NasFreights;

use App\Http\Controllers\Shared\UserManagementController;

class UserController extends UserManagementController
{
    protected function routePrefix(): string { return 'nas-freights'; }
}
