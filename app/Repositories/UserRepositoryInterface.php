<?php


namespace App\Repositories;


interface UserRepositoryInterface
{
    /**
     * @param $userMail
     * @return mixed
     */
    public function whereEmail($userMail);

    /**
     * @param $idx
     * @return mixed
     */
    public function whereIdx($idx);

    /**
     * @param $githubId
     * @return mixed
     */
    public function whereGithub($githubId);

    /**
     * @return mixed
     */
    public function all();
}
