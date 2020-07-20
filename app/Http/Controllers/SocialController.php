<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\SocialiteManager;

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
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function handleProviderCallback($provider)
    {
        $loginUser = Socialite::driver($provider)->user();
        $token = $loginUser->token;

        $user = (\App\User::whereEmail($loginUser->getEmail())->first());
        if(! $user) {
            // 새로운 사용자 추가
            $id = DB::table('users')->insertGetId([
                'name' => $loginUser->getName() ?: 'unknown',
                'email' => $loginUser->getEmail(),
                'github_id' => $loginUser->getNickname(),
                'access_token' => $token,
                'github_url' => $loginUser->user['html_url'],
                'blog_url' => $loginUser->user['blog'] ?: '',
                'avatar' => $loginUser->getAvatar(),
                'updated_dt' => now(),
                'created_dt' => now()
            ]);
            $user = \App\User::where('id', $id)->first();
        }
        auth()->login($user);
        return redirect(route('home'));
    }
}
