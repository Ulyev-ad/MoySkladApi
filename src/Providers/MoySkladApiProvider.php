<?php

namespace Dobro\MoySkladApi\Providers;

use Dobro\MoySkladApi\CustomEntity;
use Dobro\MoySkladApi\Order;
use Dobro\MoySkladApi\Product;
use Dobro\MoySkladApi\ProductFolder;
use Illuminate\Support\ServiceProvider;

class MoySkladApiProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('ProductFolder', function(){
            return new ProductFolder();
        });
        $this->app->bind('Product', function(){
            return new Product();
        });
        $this->app->bind('CustomEntity', function(){
            return new CustomEntity();
        });
        $this->app->bind('Order', function(){
            return new Order();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    }
}
