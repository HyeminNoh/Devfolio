<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    /**
     * SocialController constructor.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Handle social login process.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $provider
     * @return \App\Http\Controllers\Response
     */
    public function execute(Request $request, $provider)
    {
        if (! $request->has('code')) {
            return $this->redirectToProvider($provider);
        }

        return $this->handleProviderCallback($provider);
    }

    /**
     * Redirect the user to the Social Login Provider's authentication page.
     *
     * @param string $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from the Social Login Provider.
     *
     * @param string $provider
     * @return RedirectResponse|Redirector
     */
    protected function handleProviderCallback($provider)
    {
        try{
            $loginUser = Socialite::driver($provider)->user();
            $token = $loginUser->token;

            $user = (User::whereEmail($loginUser->getEmail())->first());
            if(! $user) {
                // 새로운 사용자 추가
                $id = DB::table('users')->insertGetId([
                    'name' => $loginUser->getName() ?: $loginUser->getNickname(),
                    'email' => $loginUser->getEmail(),
                    'github_id' => $loginUser->getNickname(),
                    'access_token' => $token,
                    'github_url' => $loginUser->user['html_url'],
                    'blog_url' => $loginUser->user['blog'] ?: '',
                    'avatar' => $loginUser->getAvatar(),
                    'updated_dt' => now(),
                    'created_dt' => now()
                ]);
                Log::info('Sign Up: '.$loginUser->getEmail());

                $user = User::where('id', $id)->first();
            }
            auth()->login($user);
            Log::info('Sign in: '.auth()->user()->name);
            return redirect(route('home'));
        } catch (\Exception $e){
            Log::info('Gihub Login Fail');
            Log::debug('Github Login Error Message');
            return redirect('/');
        }
    }
}
