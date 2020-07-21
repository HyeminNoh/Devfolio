<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RepositoryController extends Controller
{
    public function getData(){
        // 사용자 토큰 활용 api call
        $endpoint = "https://api.github.com/graphql";
        $authToken = auth()->user()->access_token;
        $query = 'query {
                    user(login: "'.auth()->user()->github_id.'") {
                        email
                        pinnedItems(first: 6, types: [REPOSITORY]) {
                            totalCount
                            edges {
                              node {
                                ... on Repository {
                                  name
                                  description
                                  forkCount
                                  stargazers {
                                    totalCount
                                  }
                                  url
                                  homepageUrl
                                  id
                                  diskUsage
                                  primaryLanguage {
                                    name
                                    color
                                  }
                                  languages(first: 10){
                                    totalCount
                                    totalSize
                                    edges{
                                      node{
                                        name
                                        color
                                      }
                                      size
                                    }
                                  }
                                }
                              }
                            }
                          }
                        }
                      }
                ';

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

            Log::info('Calling API for Pinned Repository Data Success');

            // 데이터 파싱
            $response_array = json_decode($response);

            // Log::info($response);
            $response = $response_array->data->user->pinnedItems;

            return json_encode($response);

        } catch (GuzzleException $e) {
            // api 오류 처리
            Log::info("Calling API for Pinned Repository Data Fail");
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
                DB::table('repositories')->insert([
                    'user_idx'=>auth()->user()->id ,
                    'data'=>$response,
                    'created_dt'=>now(),
                    'updated_dt'=>now()
                ]);
                Log::info('Insert Pinned Repository Data Success');
            } catch(QueryException $e){
                Log::info('Insert Pinned Repository Data Fail');
                Log::debug("Insert Error Message: \n".$e);
            }
        }
    }

    public function update()
    {
        $response = $this->getData();
        if($response){
            try{
                DB::table('repositories')->where('user_idx', auth()->user()->id)->update(['data'=>$response,'updated_dt'=>now()]);
                Log::info('Update Pinned Repository Data Success');
            } catch (QueryException $e){
                Log::info('Update Pinned Repository Data Fail');
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
        $check_data = \App\repository::where('user_idx', auth()->user()->id)->first();

        // 데이터가 아예 없을 경우 생성
        if(! $check_data){
            $this->store();
        }

        // 조회
        try{
            $userdata = DB::table('repositories')
                ->select(['data','updated_dt'])
                ->where('user_idx', auth()->user()->id)
                ->get()->toJson();

            Log::info('Select Pinned Repository Data Success');
            return $userdata;
        } catch (QueryException $e){
            Log::info('Select Pinned Repository Data Fail');
            Log::debug("Select Error Message: \n".$e);
        }
    }
}
