<?php

namespace Dobro\MoySkladApi;

use Mockery\Matcher\Any;
use PhpParser\Node\Expr\Cast\Object_;
use stdClass;

/**
 * @property  req
 */
class ProductFolder extends MoySklad
{
    protected $path = "entity/productfolder";
}
