<?php


namespace App\Factories;

abstract class AbstractFactoryMethod
{
    /**
     * @param $userIdx
     * @param $type
     * @return mixed
     */
    abstract function makeReport($userIdx, $type);
}
