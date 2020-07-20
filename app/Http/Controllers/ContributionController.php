<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $client = new \GuzzleHttp\Client();
        $response = $client->request(
            'post',
            $endpoint,
            [
                'headers'=>[
                    'Authorization' => "Bearer {$authToken}",
                    'Accept' => 'application/json'
                ],
                'json'=>[
                    'query' => $query,
                ]
            ]
        )->getBody();

        // \Log::info(sprintf($response));

        // 데이터 파싱
        $response_array = json_decode($response);
        $response = $response_array->data->user->contributionsCollection->contributionCalendar;

        return json_encode($response);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function store()
    {
        $response = $this->getData();
        DB::table('contributions')->insert([
            'user_idx'=>auth()->user()->id ,
            'data'=>$response,
            'created_dt'=>now(),
            'updated_dt'=>now()
        ]);
    }

    public function update()
    {
        $response = $this->getData();
        DB::table('contributions')->update(['data'=>$response,'updated_dt'=>now()]);
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

        if(! $check_data){
            $this->store();
        }
        // select * from contributions where user_idx = $user_idx
        $user_data = DB::table('contributions')
            ->select(['data','updated_dt'])
            ->where('user_idx', auth()->user()->id)
            ->get()->toJson();

        return $user_data;
    }
}
