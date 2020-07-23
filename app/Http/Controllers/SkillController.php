<?php

namespace App\Http\Controllers;

use App\Skill;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class SkillController extends Controller
{
    public function getData()
    {
        // 사용자 토큰 활용 api call
        $endpoint = "https://api.github.com/graphql";
        $authToken = auth()->user()->access_token;
        $query = 'query {
                    repositoryOwner (login: "' . auth()->user()->github_id . '") {
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
            $responseArray = json_decode($response);
            $parsedValue = $this->parsing($responseArray->data->repositoryOwner->repositories->edges);

            if (empty($parsedValue)) {
                Log::info('Parsing data is fail');
                return false;
            }
            return json_encode($parsedValue);

        } catch (GuzzleException $e) {
            // api 오류 처리
            Log::info("Calling API for Skill Data Fail");
            Log::debug("Calling API Error Message: \n" . $e);
            return false;
        }
    }

    // 전체 reposit의 language 정보 파싱
    public function parsing(array $data)
    {
        $noDuplication = array();
        $resultArray = array();

        if (empty($data)) {
            Log::info('Data for parsing is empty');
            return false;
        }

        foreach ($data as $obj) {
            $languages = $obj->node->languages->edges;
            foreach ($languages as $lang) {
                $langName = $lang->node->name;
                $langSize = $lang->size;
                if (!array_key_exists($langName, $noDuplication)) {
                    $langColor = $lang->node->color;
                    $noDuplication[$langName] = array(
                        'name' => $langName,
                        'color' => $langColor,
                        'size' => $langSize
                    );
                } else {
                    $noDuplication[$langName]['size'] += $langSize;
                }
            }
        }

        foreach ($noDuplication as $result) {
            array_push($resultArray, $result);
        }

        return $resultArray;
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

        if (empty($response)) {
            Log::info('Data for storing is empty');
            return false;
        }

        try {
            Skill::create([
                'user_idx' => auth()->user()->idx,
                'data' => $response,
                'created_dt' => now(),
                'updated_dt' => now()
            ]);
            Log::info('Insert Skill Data Success');
            return true;
        } catch (QueryException $e) {
            Log::info('Insert Skill Data Fail');
            Log::debug("Insert Error Message: \n" . $e);
            return false;
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
        if (empty($response)) {
            Log::info('Data for updating is empty');
            return false;
        }
        try {
            Skill::where('user_idx', auth()->user()->idx)
                ->update(['data' => $response, 'updated_dt' => now()]);
            Log::info('Update Skill Data Success');
            return true;
        } catch (QueryException $e) {
            Log::info('Update Skill Data Fail');
            Log::debug("Update Error Message: \n" . $e);
            return false;
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
        $check_data = Skill::where('user_idx', auth()->user()->idx)->first();

        // 데이터가 아예 없을 경우 생성
        if (empty($check_data)) {
            $this->store();
        }

        // 조회
        try {
            $userdata = Skill::select(['data', 'updated_dt'])
                ->where('user_idx', auth()->user()->idx)
                ->get()->toJson();

            Log::info('Select Skill Data Success');
            return $userdata;
        } catch (QueryException $e) {
            Log::info('Select Skill Data Fail');
            Log::debug("Select Error Message: \n" . $e);
            return false;
        }
    }
}
