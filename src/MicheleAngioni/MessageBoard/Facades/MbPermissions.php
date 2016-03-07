<?php

namespace MicheleAngioni\MessageBoard\Facades;

use Illuminate\Support\Facades\Facade;

class MbPermissions extends Facade
{
    protected static function getFacadeAccessor() { return 'mbpermissions'; }
}
