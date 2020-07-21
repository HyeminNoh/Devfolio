<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContributionController extends Controller
{
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
            $response_array = json_decode($response);
            $response = $response_array->data->user->contributionsCollection->contributionCalendar;

            return json_encode($response);

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
     * @return void
     */
    public function store()
    {
        $response = $this->getData();
        if ($response){
            try {
                DB::table('contributions')->insert([
                    'user_idx'=>auth()->user()->id ,
                    'data'=>$response,
                    'created_dt'=>now(),
                    'updated_dt'=>now()
                ]);
                Log::info('Insert Contribution Data Success');
            } catch(QueryException $e){
                Log::info('Insert Contribution Data Fail');
                Log::debug("Insert Error Message: \n".$e);
            }
        }
    }

    public function update()
    {
        $response = $this->getData();
        if($response){
            try{
                DB::table('contributions')->where('user_idx', auth()->user()->id)->update(['data'=>$response,'updated_dt'=>now()]);
                Log::info('Update Contribution Data Success');
            } catch (QueryException $e){
                Log::info('Update Contribution Data Fail');
                Log::debug("Update Error Message: \n".$e);
            }
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
        $check_data = \App\Contribution::where('user_idx', auth()->user()->id)->first();

        // 데이터가 아예 없을 경우 생성
        if(! $check_data){
            $this->store();
        }

        // 조회
        try{
            $userdata = DB::table('contributions')
                ->select(['data','updated_dt'])
                ->where('user_idx', auth()->user()->id)
                ->get()->toJson();

            Log::info('Select Contribution Data Success');
            return $userdata;
        } catch (QueryException $e){
            Log::info('Select Contribution Data Fail');
            Log::debug("Select Error Message: \n".$e);
        }
    }
}
