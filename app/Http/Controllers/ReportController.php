<?php

namespace App\Http\Controllers;

use App\Repositories\ReportRepository;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class ReportController extends Controller
{
    protected $reportRepo;
    protected $userRepo;

    /**
     * ReportController constructor.
     * @param ReportRepository $reportRepo
     * @param UserRepository $userRepo
     */
    public function __construct(ReportRepository $reportRepo, UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
        $this->reportRepo = $reportRepo;
    }

    /**
     * Show user portfolio view
     *
     * @param $githubId
     * @return Application|Factory|RedirectResponse|View
     */
    public function show($githubId){
        $user = $this->userRepo->whereGithub($githubId);
        if(empty($user)){
            Alert::warning('Not Found User', 'The user you are looking for was not found');
            return redirect()->back();
        }
        return view('portfolio', ['user' => $user]);
    }

    /**
     * Renew user report data
     *
     * @param $userIdx
     * @param string $type
     * @return bool
     */
    public function update($userIdx, $type)
    {
        // 로그인 상태 체크 및 업데이트 요청 사용자가 현재 로그인한 사용자 인지 체크
        if(!auth()->check() || auth()->user()->idx != $userIdx){
            Log::info('Unauthorized user request data update');
            return json_encode(array('success'=>false, 'message' => "Unauthorized user's request"));
        }

        return $this->reportRepo->update($userIdx, $type);
    }

    /**
     * Return user report data
     *
     * @param $userIdx
     * @param string $type
     * @return string
     */
    public function get($userIdx, $type)
    {
        return $this->reportRepo->get($userIdx, $type);
    }
}
