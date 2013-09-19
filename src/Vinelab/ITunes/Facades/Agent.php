<?php namespace Vinelab\iTunes\Facades;

use Illuminate\Support\Facades\Facade;

Class Agent extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'vinelab.itunes'; }

}