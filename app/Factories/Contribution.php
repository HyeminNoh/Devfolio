<?php

namespace App\Factories;

use App\Repositories\UserRepository;
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
        AbstractReport::__construct(new UserRepository(), $userIdx);
        $this->setData();
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
