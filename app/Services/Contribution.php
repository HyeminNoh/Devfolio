<?php

namespace App\Services;

use DateTime;
use Exception;
use Illuminate\Support\Facades\Log;

class Contribution extends AbstractReport
{
    private $colors;
    private $totalContributions;
    private $dailyData;

    /**
     * Return contribution instance value
     *
     * @return false|mixed|string
     */
    public function getData()
    {
        return json_encode([
            'colors' => $this->colors,
            'totalContributions' => $this->totalContributions,
            'dailyData' => $this->dailyData
        ]);
    }

    /**
     * Fill contribution instance value
     *
     * @return mixed|void
     * @throws Exception
     */
    public function setData()
    {
        $query = 'query {
                    user(login: "' . $this->githubId . '") {
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
        $apiResponse = $this->callGraphql($this->githubToken, $query, 'Contribution');
        if(empty($apiResponse)){
            Log::info('Set Contribution instance data is fail');
            return false;
        }
        return $this->parseData($apiResponse);
    }

    /**
     * parse contribution data from github graphql response
     *
     * @param $apiResponse
     * @return mixed|void
     * @throws Exception
     */
    public function parseData($apiResponse)
    {
        $inputData = $apiResponse->data->user->contributionsCollection->contributionCalendar;
        if(empty($inputData)){
            Log::info('Contribution data for parsing is empty');
            return false;
        }

        $this->colors = $inputData->colors;
        $this->totalContributions = $inputData->totalContributions;

        $this->dailyData = array();
        foreach ($inputData->weeks as $week) {
            foreach ($week->contributionDays as $day) {
                $timeToSeconds = new Datetime($day->date);
                $this->dailyData[$timeToSeconds->format('U')] = $day->contributionCount;
            }
        }
        return true;
    }
}
