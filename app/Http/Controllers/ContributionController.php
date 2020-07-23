<?php

namespace App\Http\Controllers;

use App\Contribution;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ContributionController extends Controller
{
    // protected $loginUser;
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
            $parsedValue = $this->parsing($responseArray->data->user->contributionsCollection->contributionCalendar);

            if (empty($parsedValue)) {
                Log::info('Parsing data is fail');
                return false;
            }
            return json_encode($parsedValue);

        } catch (GuzzleException $e) {
            // api 오류 처리
            Log::info("Calling API for Contribution Calendar Data Fail");
            Log::debug("Calling API Error Message: \n" . $e);
            return false;
        }
    }

    public function parsing(object $data)
    {
        $dailyData = array();
        $colors = $data->colors;
        $totalContributions = $data->totalContributions;

        if (empty($data)) {
            Log::info('Data for parsing is empty');
            return false;
        }

        foreach ($data->weeks as $week) {
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
            Contribution::create([
                'user_idx' => auth()->user()->idx,
                'data' => $response,
                'created_dt' => $nowDt,
                'updated_dt' => $nowDt
            ]);
            Log::info('Insert Contribution Data Success');
            return true;
        } catch (QueryException $e) {
            Log::info('Insert Contribution Data Fail');
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
            Contribution::where('user_idx', auth()->user()->idx)
                ->update(['data' => $response, 'updated_dt' => now()]);
            Log::info('Update Contribution Data Success');
            return true;
        } catch (QueryException $e) {
            Log::info('Update Contribution Data Fail');
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
        $checkData = Contribution::where('user_idx', auth()->user()->idx)->first();

        // 데이터가 아예 없을 경우 생성
        if (empty($checkData)) {
            $this->store();
        }

        // 조회
        try {
            $userData = Contribution::select(['data', 'updated_dt'])
                ->where('user_idx', auth()->user()->idx)
                ->get()->toJson();

            Log::info('Select Contribution Data Success');
            return $userData;
        } catch (QueryException $e) {
            Log::info('Select Contribution Data Fail');
            Log::debug("Select Error Message: \n" . $e);
            return false;
        }
    }
}
