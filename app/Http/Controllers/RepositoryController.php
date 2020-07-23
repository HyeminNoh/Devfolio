<?php

namespace App\Http\Controllers;

use App\Repository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class RepositoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getData()
    {
        // 사용자 토큰 활용 api call
        $endpoint = "https://api.github.com/graphql";
        $authToken = auth()->user()->access_token;
        $query = 'query {
                    user(login: "' . auth()->user()->github_id . '") {
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
            $responseArray = json_decode($response);

            return json_encode($responseArray->data->user->pinnedItems);

        } catch (GuzzleException $e) {
            // api 오류 처리
            Log::info("Calling API for Pinned Repository Data Fail");
            Log::debug("Calling API Error Message: \n" . $e);
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

        if (empty($response)) {
            Log::info('Data for storing is empty');
            return false;
        }
        try {
            Repository::create([
                'user_idx' => auth()->user()->idx,
                'data' => $response,
                'created_dt' => $nowDt,
                'updated_dt' => $nowDt
            ]);
            Log::info('Insert Pinned Repository Data Success');
            return true;
        } catch (QueryException $e) {
            Log::info('Insert Pinned Repository Data Fail');
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
            Repository::where('user_idx', auth()->user()->idx)
                ->update(['data' => $response, 'updated_dt' => now()]);
            Log::info('Update Pinned Repository Data Success');
            return true;
        } catch (QueryException $e) {
            Log::info('Update Pinned Repository Data Fail');
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
        $checkData = Repository::where('user_idx', auth()->user()->idx)->first();

        // 데이터가 아예 없을 경우 생성
        if (empty($checkData)) {
            $this->store();
        }

        // 조회
        try {
            $userData = Repository::select(['data', 'updated_dt'])
                ->where('user_idx', auth()->user()->idx)
                ->get()->toJson();
            Log::info('Select Pinned Repository Data Success');
            return $userData;
        } catch (QueryException $e) {
            Log::info('Select Pinned Repository Data Fail');
            Log::debug("Select Error Message: \n" . $e);
            return false;
        }
    }
}
