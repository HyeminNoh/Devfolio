<?php

namespace App\Factories;

use App\User;
use DateTime;
use Exception;

class Contribution extends AbstractReport
{
    private $colors;
    private $totalContributions;
    private $dailyData;

    /**
     * Contribution constructor.
     * @param $userIdx
     * @throws Exception
     */
    public function __construct($userIdx)
    {
        $this->setData($userIdx);
    }

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
     * @param $userIdx
     * @return mixed|void
     * @throws Exception
     */
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
        $apiResponse = $this->callGithubApi($user->access_token, $query, 'Contribution');
        $this->parseData($apiResponse);
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
        $this->colors = $inputData->colors;
        $this->totalContributions = $inputData->totalContributions;

        $this->dailyData = array();
        foreach ($inputData->weeks as $week) {
            foreach ($week->contributionDays as $day) {
                $timeToSeconds = new Datetime($day->date);
                $this->dailyData[$timeToSeconds->format('U')] = $day->contributionCount;
            }
        }
    }
}
