<?php

namespace Irabbi360\LaravelApiInspector\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Irabbi360\LaravelApiInspector\LaravelApiInspector
 */
class LaravelApiInspector extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Irabbi360\LaravelApiInspector\LaravelApiInspector::class;
    }
}
