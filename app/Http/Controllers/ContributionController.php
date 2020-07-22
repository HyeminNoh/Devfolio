<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Contribution;

class ContributionController extends Controller
{
    // protected $loginUser;
    public function __construct()
    {
        $this->middleware('auth');
        // $this->loginUser = auth()->user();
        // Log::info(json_encode($this->loginUser));
    }

    public function getData(){
        // 사용자 토큰 활용 api call
        $endpoint = "https://api.github.com/graphql";
        $authToken = auth()->user()->access_token;
        $query = 'query {
                    user(login: "'.auth()->user()->github_id.'") {
                        contributionsCollection {
                            contributionCalendar {
                                colors
                                totalContributions
                                weeks {
                                    contributionDays {
                                      color
                                      contributionCount
                                      date
                                      weekday
                                    }
                                    firstDay
                                }
                            }
                        }
                      }
                }';

        $client = new Client();
        try {
            $response = $client->request(
                'post',
                $endpoint,
                [
                    'headers' => [
                        'Authorization' => "Bearer {$authToken}",
                        'Accept' => 'application/json'
                    ],
                    'json' => [
                        'query' => $query,
                    ]
                ]
            )->getBody();

            Log::info('Calling API for Contribution Calendar Data Success');

            // 데이터 파싱
            $responseArray = json_decode($response);

            return json_encode($responseArray->data->user->contributionsCollection->contributionCalendar);

        } catch (GuzzleException $e) {
            // api 오류 처리
            Log::info("Calling API for Contribution Calendar Data Fail");
            Log::debug("Calling API Error Message: \n".$e);
            return false;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return bool
     */
    public function store()
    {
        $response = $this->getData();
        $nowDt = now();

        if (empty($response)){
            Log::info('Storing data is fail');
            return false;
        }

        try {
            Contribution::create([
                'user_idx'=>auth()->user()->idx ,
                'data'=>$response,
                'created_dt'=>$nowDt,
                'updated_dt'=>$nowDt
            ]);
            Log::info('Insert Contribution Data Success');
            return true;
        } catch(QueryException $e){
            Log::info('Insert Contribution Data Fail');
            Log::debug("Insert Error Message: \n".$e);
        }
    }

    /**
     * Update a existed resource in storage.
     *
     * @return bool
     */
    public function update()
    {
        $response = $this->getData();
        if(empty($response)) {
            Log::info('Updating data is fail');
            return false;
        }
        try{
            Contribution::where('user_idx', auth()->user()->idx)->update(['data'=>$response,'updated_dt'=>now()]);
            Log::info('Update Contribution Data Success');
            return true;
        } catch (QueryException $e){
            Log::info('Update Contribution Data Fail');
            Log::debug("Update Error Message: \n".$e);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return string
     */
    public function show()
    {
        // 사용자 아이디 기반 조회
        $checkData = Contribution::where('user_idx', auth()->user()->idx)->first();

        // 데이터가 아예 없을 경우 생성
        if(! $checkData){
            $this->store();
        }

        // 조회
        try{
            $userData = Contribution::select(['data','updated_dt'])
                ->where('user_idx', auth()->user()->idx)
                ->get()->toJson();

            Log::info('Select Contribution Data Success');
            return $userData;
        } catch (QueryException $e){
            Log::info('Select Contribution Data Fail');
            Log::debug("Select Error Message: \n".$e);
        }
    }
}
