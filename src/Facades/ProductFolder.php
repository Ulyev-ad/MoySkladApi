<?php


namespace Dobro\MoySkladApi\Facades;

use Illuminate\Support\Facades\Facade;

class ProductFolder  extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ProductFolder';
    }
}
