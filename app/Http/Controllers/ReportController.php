<?php

namespace App\Http\Controllers;

use App\Repositories\ReportRepository;

class ReportController extends Controller
{
    protected $reportRepo;

    public function __construct(ReportRepository $reportRepo)
    {
        $this->reportRepo = $reportRepo;
    }

    /**
     * Redirect the user to the Social Login Provider's authentication page.
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
     * Redirect the user to the Social Login Provider's authentication page.
     *
     * @param $userIdx
     * @param string $type
     * @return string
     */
    public function show($userIdx, $type)
    {
        return $this->reportRepo->get($userIdx, $type);
    }
}
