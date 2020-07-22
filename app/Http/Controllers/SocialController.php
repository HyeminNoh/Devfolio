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
            // oauth 정보 로드
            $socialData = Socialite::driver($provider)->user();

            // 사용자 존재 체크
            $user = (User::whereEmail($socialData->getEmail())->first());
            if(! $user) {
                // 새로운 사용자 추가
                $user = User::create([
                    'name' => $socialData->getName() ?: $socialData->getNickname(),
                    'email' => $socialData->getEmail(),
                    'github_id' => $socialData->getNickname(),
                    'access_token' => $socialData->token,
                    'github_url' => $socialData->user['html_url'],
                    'blog_url' => $socialData->user['blog'] ?: '',
                    'avatar' => $socialData->getAvatar(),
                    'updated_dt' => now(),
                    'created_dt' => now()
                ]);
                Log::info('Sign Up: '.$socialData->getEmail());
            }
            auth()->login($user);
            Log::info('Sign in: '.auth()->user()->name);
            return redirect(route('home'));
        } catch (\Exception $e){
            Log::info('Github Login Fail');
            Log::debug('Github Login Error Message');
            return redirect('/');
        }
    }
}
