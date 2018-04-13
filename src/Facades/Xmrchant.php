<?php

namespace JK3Y\Xmrchant\Facades;

use Illuminate\Support\Facades;

class Xmrchant extends Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'JK3Y-xmrchant';
    }
}