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
     * @param $githubId
     * @return mixed
     */
    public function getGithub($githubId);

    /**
     * @return mixed
     */
    public function all();
}
