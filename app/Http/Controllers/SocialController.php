<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use RealRashid\SweetAlert\Facades\Alert;

class SocialController extends Controller
{
    protected $userRepo;

    /**
     * SocialController constructor.
     * @param UserRepository $userRepo
     */
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
        $this->middleware('guest');
    }

    /**
     * Handle social login process.
     *
     * @param Request $request
     * @param string $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function execute(Request $request, $provider)
    {
        if (!$request->has('code')) {
            return $this->redirectToProvider($provider);
        }

        // oauth 인증 완료 후 code 포함 request 회신
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
        switch ($provider){
            case 'github':
                return Socialite::driver($provider)
                    ->scopes(['read:user', 'public_repo'])
                    ->redirect();
            default:
                Alert::warning('Undefined social type');
                return redirect('/');
        }
    }

    /**
     * Obtain the user information from the Social Login Provider.
     *
     * @param string $provider
     * @return RedirectResponse|Redirector
     */
    protected function handleProviderCallback($provider)
    {
        // oauth 정보 Socialite 통해 확인 가능
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

        $userMail = $socialData->getEmail();

        // 사용자 등록 여부 확인
        $user = $this->userRepo->whereEmail($userMail);

        // 새로운 사용자 추가
        if (empty($user)) {
            $newUser = $this->userRepo->create($socialData);
            if(empty($newUser)){
                Alert::error('Sign Up Fail');
                return redirect('/');
            }
            $user = $newUser;
            Alert::success('Sign up Success');
            Log::info('Sign Up: ' . $socialData->getEmail());
        }
        auth()->login($user);
        Log::info('Sign in: ' . auth()->user()->name);
        return redirect()->back();
    }
}
