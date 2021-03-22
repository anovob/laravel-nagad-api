<?php 
namespace NagadAPI\Facades;

use Illuminate\Support\Facades\Facade;

class Nagad extends Facade
{    
    /**
     * getFacadeAccessor
     *
     * @return void
     */
    protected static function getFacadeAccessor()
    {
        return 'nagad';
    }
}