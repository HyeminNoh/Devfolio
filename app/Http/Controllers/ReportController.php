<?php

namespace App\Http\Controllers;

use App\Repositories\ReportRepository;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class ReportController extends Controller
{
    /**
     * @var ReportRepository
     */
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
        $user = $this->userRepo->getGithub($githubId);
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
