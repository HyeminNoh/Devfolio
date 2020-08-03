<?php


namespace App\Repositories;


use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UserRepository
{
    private $user;
    public function __construct()
    {
        $this->user = new User;
    }

    /**
     * Obtain the user information by data table idx.
     *
     * @param $idx
     * @return User|User[]|Collection|Model|null
     */
    public function get($idx){
        return $this->user->find($idx);
    }

    /**
     * Obtain the user information by User's gitub id.
     *
     * @param $githubId
     * @return mixed
     */
    public function getGithub($githubId){
        return $this->user->where(['github_id' => $githubId])->first();
    }

    /**
     * Obtain all user's information in random order
     *
     * @return User[]|Collection
     */
    public function all(){
        return $this->user->inRandomOrder()->get();
    }
}
