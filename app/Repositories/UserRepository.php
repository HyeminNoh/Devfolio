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
     * @param $idx
     * @return User|User[]|Collection|Model|null
     */
    public function get($idx){
        return $this->user->find($idx);
    }

    /**
     * @return User[]|Collection
     */
    public function all(){
        return $this->user->inRandomOrder()->get();
    }
}
