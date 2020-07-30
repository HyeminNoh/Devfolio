<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
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
     * @param Request $request
     * @param string $provider
     * @return Response
     */
    public function execute(Request $request, $provider)
    {
        if (!$request->has('code')) {
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
        return Socialite::driver($provider)
            ->scopes(['read:user', 'public_repo'])
            ->redirect();
    }

    /**
     * Obtain the user information from the Social Login Provider.
     *
     * @param string $provider
     * @return RedirectResponse|Redirector
     */
    protected function handleProviderCallback($provider)
    {
        try {
            // oauth 정보 로드
            $socialData = Socialite::driver($provider)->user();

            // 필수 정보 조회 성공 여부 확인
            if (empty($socialData->token)) {
                Log::info('Loading' . $provider . ' access token is fail');
                return redirect('/');
            }

            if (empty($socialData->getEmail())) {
                Log::info('Loading' . $provider . ' user email is fail');
                return redirect('/');
            }

            // 중복 사용 되는 값 변수 처리
            $userMail = $socialData->getEmail();
            $userNickname = $socialData->getNickname();
            $nowDt = now();

            // 사용자 등록 여부 확인
            $user = (User::whereEmail($userMail)->first());

            // 새로운 사용자 추가
            if (!$user) {
                $user = User::create([
                    'name' => $socialData->getName() ?: $userNickname,
                    'email' => $userMail,
                    'github_id' => $userNickname,
                    'access_token' => $socialData->token,
                    'github_url' => $socialData->user['html_url'] ?: '',
                    'blog_url' => $socialData->user['blog'] ?: '',
                    'avatar' => $socialData->getAvatar() ?: '',
                    'updated_dt' => $nowDt,
                    'created_dt' => $nowDt
                ]);
                Log::info('Sign Up: ' . $socialData->getEmail());
            }
            auth()->login($user);
            Log::info('Sign in: ' . auth()->user()->name);
            return redirect(route('portfolio/' . auth()->user()->idx));
        } catch (Exception $e) {
            Log::info('Github Login Fail');
            Log::debug('Github Login Error Message');
            return redirect('/');
        }
    }
}
