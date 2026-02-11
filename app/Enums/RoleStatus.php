<?php

namespace App\Enums;

enum RoleStatus: string
{
    case ADMIN = 'admin';
    case CUSTOMER = 'customer';
    case VENDOR = 'vendor';
}
