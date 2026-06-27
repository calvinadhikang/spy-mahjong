<?php

namespace App\Enums;

enum IdentityProvider: string
{
    case Password = 'password';
    case Google = 'google';
}
