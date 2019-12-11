<?php


namespace Dobro\MoySkladApi\Facades;


use Illuminate\Support\Facades\Facade;

class MoySklad extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'MoySklad';
    }
}
