<?php

namespace App\Factories;

use App\User;
use DateTime;

class Contribution extends AbstractReport
{
    private $colors;
    private $totalContributions;
    private $dailyData;

    public function __construct($userIdx)
    {
        $this->setData($userIdx);
    }

    public function getData()
    {
        return json_encode([
            'colors' => $this->colors,
            'totalContributions' => $this->totalContributions,
            'dailyData' => $this->dailyData
        ]);
    }

    public function setData($userIdx)
    {
        $user = User::find($userIdx);
        $query = 'query {
                    user(login: "' . $user->github_id . '") {
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
        $apiResponse = $this->callApi($user->access_token, $query, 'Contribution');
        $this->parseData($apiResponse);
    }

    public function parseData($apiResponse)
    {
        $inputData = $apiResponse->data->user->contributionsCollection->contributionCalendar;
        $this->colors = $inputData->colors;
        $this->totalContributions = $inputData->totalContributions;

        $this->dailyData = array();
        foreach ($inputData->weeks as $week) {
            foreach($week->contributionDays as $day){
                $timeToSeconds = new Datetime($day->date);
                $this->dailyData[$timeToSeconds->format('U')] = $day->contributionCount;
            }
        }
    }
}
