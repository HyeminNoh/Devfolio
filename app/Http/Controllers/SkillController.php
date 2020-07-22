<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SkillController extends Controller
{
    public function getData(){
        // 사용자 토큰 활용 api call
        $endpoint = "https://api.github.com/graphql";
        $authToken = auth()->user()->access_token;
        $query = 'query {
                    repositoryOwner (login: "'.auth()->user()->github_id.'") {
                        repositories(first:100) {
                            totalCount
                            edges{
                                node{
                                    languages(first: 10){
                                        totalCount,
                                        totalSize
                                        edges{
                                            node{
                                                name,
                                                color,
                                            }
                                            size
                                        }
                                    }
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

            Log::info('Calling API for Pinned Repository Data Success');

            // 데이터 파싱
            $response_array = json_decode($response);
            $response = $response_array->data->repositoryOwner->repositories->edges;
            $parsedValue = $this->parsing($response);

            return json_encode($parsedValue);

        } catch (GuzzleException $e) {
            // api 오류 처리
            Log::info("Calling API for Skill Data Fail");
            Log::debug("Calling API Error Message: \n".$e);
            return false;
        }
    }

    // 전체 reposit의 language 정보 파싱
    public function parsing(array $data){
        $noDuplication = array();

        foreach ($data as $obj){
            // Log::info(json_encode($obj));
            $languages = $obj->node->languages->edges;
            foreach ($languages as $lang){
                $langName = $lang->node->name;
                $langSize = $lang->size;
                if(! array_key_exists($langName, $noDuplication)){
                    $langColor = $lang->node->color;
                    $noDuplication[$langName] = array(
                        'name' => $langName,
                        'color'=>$langColor,
                        'size'=>$langSize
                    );
                } else {
                    $noDuplication[$langName]['size'] += $langSize;
                }
            }
        }

        $resultArray = array();
        foreach ($noDuplication as $result){
            array_push($resultArray, $result);
        }

        return $resultArray;
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
                DB::table('skills')->insert([
                    'user_idx'=>auth()->user()->idx ,
                    'data'=>$response,
                    'created_dt'=>now(),
                    'updated_dt'=>now()
                ]);
                Log::info('Insert Skill Data Success');
            } catch(QueryException $e){
                Log::info('Insert Skill Data Fail');
                Log::debug("Insert Error Message: \n".$e);
            }
        }
    }

    public function update()
    {
        $response = $this->getData();
        if($response){
            try{
                DB::table('skills')->where('user_idx', auth()->user()->idx)->update(['data'=>$response,'updated_dt'=>now()]);
                Log::info('Update Skill Data Success');
            } catch (QueryException $e){
                Log::info('Update Skill Data Fail');
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
        $check_data = \App\Skill::where('user_idx', auth()->user()->idx)->first();

        // 데이터가 아예 없을 경우 생성
        if(! $check_data){
            $this->store();
        }

        // 조회
        try{
            $userdata = DB::table('skills')
                ->select(['data','updated_dt'])
                ->where('user_idx', auth()->user()->idx)
                ->get()->toJson();

            Log::info('Select Skill Data Success');
            return $userdata;
        } catch (QueryException $e){
            Log::info('Select Skill Data Fail');
            Log::debug("Select Error Message: \n".$e);
        }
    }
}
