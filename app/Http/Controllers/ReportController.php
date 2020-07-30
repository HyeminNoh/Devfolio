<?php

namespace App\Http\Controllers;

use App\Repositories\ReportRepository;

class ReportController extends Controller
{
    /**
     * @var ReportRepository
     */
    protected $reportRepo;

    /**
     * ReportController constructor.
     * @param ReportRepository $reportRepo
     */
    public function __construct(ReportRepository $reportRepo)
    {
        $this->reportRepo = $reportRepo;
    }

    /**
     * Renew user's report data
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
     * Returing user's report data
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
