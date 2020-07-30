<?php


namespace App\Repositories;

use App\Factories\ReportFactoryMethod;
use App\Report;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ReportRepository implements ReportRepositoryInterface
{
    protected $reportFactory;

    public function __construct()
    {
        // factory 생성
        $this->reportFactory = new ReportFactoryMethod;
    }

    // 요청 타입에 맞는 데이터 조회
    public function get($userIdx, $type){
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
            Log::info('Select '.$type.' Data Success');
            return $userData;
        } catch (QueryException $e) {
            Log::info('Select '.$type.' Data Fail');
            Log::debug("Select Error Message: \n" . $e);
            return false;
        }
    }

    // 데이터 수정
    public function update($userIdx, $type){// instance 생성
        $report = $this->reportFactory->makeReport($userIdx, $type);
        $response = $report->getData($type);

        if (empty($response)) {
            Log::info('Data for updating is empty');
            return false;
        }
        try {
            Report::where(['user_idx' => $userIdx, 'type' => $type])
                ->update(['data' => $response, 'updated_dt' => now()]);
            Log::info('Update '.$type.' Data Success');
            return true;
        } catch (QueryException $e) {
            Log::info('Update '.$type.' Data Fail');
            Log::debug("Update Error Message: \n" . $e);
            return false;
        }
    }

    // 데이터 신규 등록
    private function store($userIdx, $type){
        $user = User::find($userIdx);
        if($type=='blog' && empty($user->blog_url)){
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
            Log::info('Insert '.$type.' Data Success');
            return true;
        } catch (QueryException $e) {
            Log::info('Insert '.$type.' Data Fail');
            Log::debug("Insert Error Message: \n" . $e);
            return false;
        }
    }
}
