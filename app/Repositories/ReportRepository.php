<?php


namespace App\Repositories;

use App\Services\ReportFactoryMethod;
use App\Report;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ReportRepository implements ReportRepositoryInterface
{
    protected $reportFactory;
    protected $report;
    protected $userRepo;
    protected $typeList;

    /**
     * ReportRepository constructor.
     */
    public function __construct()
    {
        $this->reportFactory = new ReportFactoryMethod;
        $this->report = new Report;
        $this->userRepo = new UserRepository();
        $this->typeList = array('blog', 'contribution', 'skill', 'repository');
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
        // 사용자 존재 체크
        if(empty($this->userRepo->whereIdx($userIdx))){
            Log::info('Get report data is fail. user index '.$userIdx.' is not exist.');
            return false;
        }

        // type 유효성 체크
        if(!in_array($type, $this->typeList)){
            Log::info('Get report data is fail. Undefined type report');
            return false;
        }

        // 매개변수는 유효하지만 데이터가 존재하지 않을 때
        $isReport = $this->report->where(['user_idx' => $userIdx, 'type' => $type])->exists();
        if (empty($isReport)) {
            $this->store($userIdx, $type);
        }

        // 마지막 업데이트 날짜 체크
        $now = now();
        $lastUpdated = $this->report->where(['user_idx' => $userIdx, 'type' => $type])->first()->updated_dt;
        $interval = $now->diff($lastUpdated)->days;
        if(1<=$interval){
            $this->update($userIdx, $type);
        };

        // 조회
        try {
            $userData = $this->report->select(['data', 'updated_dt'])
                ->where(['user_idx' => $userIdx, 'type' => $type])
                ->get()->toJson();
            Log::info('user index '.$userIdx.': Select ' . $type . ' Data Success');
            return $userData;
        } catch (QueryException $e) {
            Log::info('user index '.$userIdx.': Select ' . $type . ' Data Fail');
            Log::error('user index '.$userIdx.': Select '.$type."\n Data Error Message: ".$e);
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
    {
        // 사용자 존재 체크
        if(empty($this->userRepo->whereIdx($userIdx))){
            Log::info('Update report data is fail - '.$userIdx.' is not exist.');
            return false;
        }

        // type 유효성 체크
        if(!in_array($type, $this->typeList)){
            Log::info('Update report data is fail - Undefined type report');
            return false;
        }

        // instance 생성
        $report = $this->reportFactory->makeReport($userIdx, $type);
        $response = $report->getData($type);

        // 대체할 데이터가 비어 있을 시
        if (empty($response)) {
            Log::info('Data for updating is empty');
            return false;
        }
        try {
            $this->report->where(['user_idx' => $userIdx, 'type' => $type])
                ->update(['data' => $response, 'updated_dt' => now()]);
            Log::info('Update ' . $type . ' Data Success');
            return json_encode(array('success' => true, 'message' => $type." Data Update Success"));
        } catch (QueryException $e) {
            Log::info('Update ' . $type . ' Data Fail');
            Log::error("Update ".$type." Data Error Message: \n" . $e);
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
        $user = $this->userRepo->whereIdx($userIdx);
        if ($type == 'blog' && empty($user->blog_url)) {
            return false;
        }

        // instance 생성
        $report = $this->reportFactory->makeReport($userIdx, $type);
        if(empty($report)){
            Log::info('Data storing is fail -  Make report instance is fail');
            return false;
        }

        $response = $report->getData();
        if (empty($response)) {
            Log::info('Data storing is fail - Instance data is empty');
            return false;
        }

        $nowDt = now();
        try {
            $this->report->create([
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
            Log::error("Insert ".$type." Data Error Message: \n" . $e);
            return false;
        }
    }
}
