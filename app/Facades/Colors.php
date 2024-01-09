<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Colors extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'nexus.colors';
    }
}
