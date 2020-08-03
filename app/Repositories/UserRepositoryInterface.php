<?php


namespace App\Repositories;


interface UserRepositoryInterface
{
    /**
     * @param $idx
     * @return mixed
     */
    public function get($idx);

    /**
     * @return mixed
     */
    public function all();
}
