<?php


namespace App\Repositories;


use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class UserRepository implements UserRepositoryInterface
{
    protected $user;

    public function __construct()
    {
        $this->user = new User;
    }

    /**
     * @param $socialData
     * @return User|bool|Model
     */
    public function create($socialData){
        $userNickname = $socialData->getNickname();
        $nowDt = now();

        try {
            $user = $this->user->create([
                'name' => $socialData->getName() ?: $userNickname,
                'email' => $socialData->getEmail(),
                'github_id' => $userNickname,
                'access_token' => $socialData->token,
                'github_url' => $socialData->user['html_url'] ?: '',
                'blog_url' => $socialData->user['blog'] ?: '',
                'avatar' => $socialData->getAvatar() ?: '',
                'updated_dt' => $nowDt,
                'created_dt' => $nowDt
            ]);
            return $user;
        } catch (QueryException $exception) {
            Log::info('Sign Up Fail');
            Log::error("Sign Up Fail Error Message: \n".$exception);
            return false;
        }
    }

    /**
     * @param $userMail
     * @return User|bool|Collection
     */
    public function whereEmail($userMail){
        $user = $this->user->where('email', $userMail)->first();
        if(empty($user)){
            Log::info($userMail.' user is not found');
            return false;
        }
        return $user;
    }

    /**
     * Obtain the user information by data table idx.
     * @param $idx
     * @return User|bool|Collection|Model
     */
    public function whereIdx($idx){
        $user = $this->user->find($idx);
        if(empty($user)){
            Log::info($idx.' user is not found');
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
    public function whereGithub($githubId){
        $user = $this->user->where(['github_id' => $githubId])->first();
        if(empty($user)){
            Log::info($githubId.' user is not found');
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
