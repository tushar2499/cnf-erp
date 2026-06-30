<?php

namespace App\Http\Controllers\NasTrading;

use App\Http\Controllers\Shared\UserManagementController;

class UserController extends UserManagementController
{
    protected function routePrefix(): string { return 'nas-trading'; }
}
