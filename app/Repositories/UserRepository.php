<?php


namespace App\Repositories;


use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class UserRepository implements UserRepositoryInterface
{
    protected $user;

    public function __construct()
    {
        $this->user = new User;
    }

    /**
     * Obtain the user information by data table idx.
     * @param $idx
     * @return User|bool|Collection|Model
     */
    public function get($idx){
        $user = $this->user->find($idx);
        if(empty($user)){
            Log::info('Get '.$idx.'user info fail');
            return false;
        }
        return $user;
    }

    /**
     * Obtain the user information by User's gitub id.
     *
     * @param $githubId
     * @return mixed
     */
    public function getGithub($githubId){
        $user = $this->user->where(['github_id' => $githubId])->first();
        if(empty($user)){
            Log::info('Get '.$githubId.'user info fail');
            return false;
        }
        return $user;
    }

    /**
     * Obtain all user's information in random order
     *
     * @return User[]|bool|Collection
     */
    public function all(){
        $userList = $this->user->inRandomOrder()->get();
        if(empty($userList)){
            Log::info('Get all users list in random fail');
            return false;
        }
        return $userList;
    }
}
