<?php


namespace App\Repositories;

use App\Factories\ReportFactoryMethod;
use App\Report;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ReportRepository implements ReportRepositoryInterface
{
    /**
     * @var ReportFactoryMethod
     */
    protected $reportFactory;

    /**
     * ReportRepository constructor.
     */
    public function __construct()
    {
        // factory 생성
        $this->reportFactory = new ReportFactoryMethod;
    }

    /**
     * Obtain the report information from report data table.
     *
     * @param $userIdx
     * @param $type
     * @return bool
     */
    public function get($userIdx, $type)
    {
        // 사용자 아이디 기반 조회
        $checkData = Report::where(['user_idx' => $userIdx, 'type' => $type])->first();

        // 데이터가 아예 없을 경우 생성
        if (empty($checkData)) {
            $this->store($userIdx, $type);
        }

        // 조회
        try {
            $userData = Report::select(['data', 'updated_dt'])
                ->where(['user_idx' => $userIdx, 'type' => $type])
                ->get()->toJson();
            Log::info('Select ' . $type . ' Data Success');
            return $userData;
        } catch (QueryException $e) {
            Log::info('Select ' . $type . ' Data Fail');
            Log::debug("Select Error Message: \n" . $e);
            return false;
        }
    }

    /**
     * Edit report data in DB
     *
     * @param $userIdx
     * @param $type
     * @return bool
     */
    public function update($userIdx, $type)
    {// instance 생성
        $report = $this->reportFactory->makeReport($userIdx, $type);
        $response = $report->getData($type);

        // 대체할 데이터가 비어 있을 시
        if (empty($response)) {
            Log::info('Data for updating is empty');
            return false;
        }
        try {
            Report::where(['user_idx' => $userIdx, 'type' => $type])
                ->update(['data' => $response, 'updated_dt' => now()]);
            Log::info('Update ' . $type . ' Data Success');
            return true;
        } catch (QueryException $e) {
            Log::info('Update ' . $type . ' Data Fail');
            Log::debug("Update Error Message: \n" . $e);
            return false;
        }
    }

    /**
     * Store new report data in DB
     *
     * @param $userIdx
     * @param $type
     * @return bool
     */
    private function store($userIdx, $type)
    {
        // blog_url이 없는 사용자는 blog 데이터를 등록할 필요가 없음
        $user = User::find($userIdx);
        if ($type == 'blog' && empty($user->blog_url)) {
            return false;
        }

        // instance 생성
        $report = $this->reportFactory->makeReport($userIdx, $type);
        $response = $report->getData();
        $nowDt = now();

        if (empty($response)) {
            Log::info('Data for storing is empty');
            return false;
        }

        try {
            Report::create([
                'user_idx' => $userIdx,
                'type' => $type,
                'data' => $response,
                'created_dt' => $nowDt,
                'updated_dt' => $nowDt
            ]);
            Log::info('Insert ' . $type . ' Data Success');
            return true;
        } catch (QueryException $e) {
            Log::info('Insert ' . $type . ' Data Fail');
            Log::debug("Insert Error Message: \n" . $e);
            return false;
        }
    }
}
