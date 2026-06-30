<?php

namespace App\Http\Controllers\Chevron;

use App\Http\Controllers\Shared\UserManagementController;

class UserController extends UserManagementController
{
    protected function routePrefix(): string { return 'chevron'; }
}
