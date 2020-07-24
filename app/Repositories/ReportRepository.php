<?php


namespace App\Repositories;

use App\Report;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReportRepository implements ReportInterface
{
    // 요청 타입에 맞는 데이터 조회
    public function show($type){
        // 사용자 아이디 기반 조회
        $checkData = Report::where(['user_idx' => auth()->user()->idx, 'type' => $type])->first();

        // 데이터가 아예 없을 경우 생성
        if (empty($checkData)) {
            $this->store($type);
        }

        // 조회
        try {
            $userData = Report::select(['data', 'updated_dt'])
                ->where(['user_idx' => auth()->user()->idx, 'type' => $type])
                ->get()->toJson();
            Log::info('Select '.$type.' Data Success');
            return $userData;
        } catch (QueryException $e) {
            Log::info('Select '.$type.' Data Fail');
            Log::debug("Select Error Message: \n" . $e);
            return false;
        }
    }

    public function update($type){
        $response = $this->getData($type);

        if (empty($response)) {
            Log::info('Data for updating is empty');
            return false;
        }
        try {
            Report::where(['user_idx' => auth()->user()->idx, 'type' => $type])
                ->update(['data' => $response, 'updated_dt' => now()]);
            Log::info('Update '.$type.' Data Success');
            return true;
        } catch (QueryException $e) {
            Log::info('Update '.$type.' Data Fail');
            Log::debug("Update Error Message: \n" . $e);
            return false;
        }
    }

    private function store($type){
        $response = $this->getData($type);
        $nowDt = now();

        if (empty($response)) {
            Log::info('Data for storing is empty');
            return false;
        }

        try {
            Report::create([
                'user_idx' => auth()->user()->idx,
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

    private function getData($type){
        // 사용자 토큰 활용 api call
        $endpoint = "https://api.github.com/graphql";
        $authToken = auth()->user()->access_token;
        $githubId = auth()->user()->github_id;
        $query = '';
        switch ($type){
            case 'contribution':
                $query = 'query {
                    user(login: "' . $githubId . '") {
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
                }';;
                break;
            case 'skill':
                $query = 'query {
                    repositoryOwner (login: "' . $githubId . '") {
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
                }';;
                break;
            case 'repo':
                $query = 'query {
                    user(login: "' . $githubId . '") {
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
                ';;
                break;
        }
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

            Log::info('Calling API for'.$type.'Data Success');

            $responseArray = json_decode($response);
            // 파싱될 데이터가 있는지 확인
            if (empty($responseArray)){
                Log::info($type.'Data for parsing is empty');
                return false;
            }
            // 데이터 파싱
            $parsedValue = $this->parsing($type, $responseArray);

            // 파싱 결과 확인
            if (empty($parsedValue)) {
                Log::info('Data parsing is fail');
                return false;
            }

            return json_encode($parsedValue);

        } catch (GuzzleException $e) {
            // api 오류 처리
            Log::info("Calling API for".$type."Data Fail");
            Log::debug("Calling API Error Message: \n" . $e);
            return false;
        }
    }

    private function parsing($type, $responseArray)
    {
        switch ($type){
            case 'contribution':
                $inputData = $responseArray->data->user->contributionsCollection->contributionCalendar;
                $colors = $inputData->colors;
                $totalContributions = $inputData->totalContributions;

                $dailyData = array();
                foreach ($inputData->weeks as $week) {
                    foreach($week->contributionDays as $day){
                        $timeToSeconds = new Datetime($day->date);
                        $dailyData[$timeToSeconds->format('U')] = $day->contributionCount;
                    }
                }

                return [
                    'colors' => $colors,
                    'totalContributions' => $totalContributions,
                    'dailyData' => $dailyData
                ];
            case 'skill':
                $inputData = $responseArray->data->repositoryOwner->repositories->edges;
                $noDuplication = array();
                $resultArray = array();

                if (empty($inputData)) {
                    Log::info('Data for parsing is empty');
                    return false;
                }

                foreach ($inputData as $obj) {
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
            case 'repo':
                return $responseArray->data->user->pinnedItems;
        }
    }
}
