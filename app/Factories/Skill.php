<?php

namespace App\Factories;

use App\User;
use Illuminate\Support\Facades\Log;

class Skill extends AbstractReport
{
    /**
     * Skill constructor.
     * @param $userIdx
     */
    public function __construct($userIdx)
    {
        $this->setData($userIdx);
    }

    /**
     * Return skill instance value
     *
     * @return false|mixed|string
     */
    public function getData()
    {
        return json_encode($this->resultArray);
    }

    /**
     * Fill skill instance value
     *
     * @param $userIdx
     * @return mixed|void
     */
    public function setData($userIdx)
    {
        $user = User::find($userIdx);
        $query = 'query {
                    repositoryOwner (login: "' . $user->github_id . '") {
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
        $apiResponse = $this->callGithubApi($user->access_token, $query, 'Skill');
        $this->parseData($apiResponse);
    }

    /**
     * Parse data from github api response
     *
     * @param $apiResponse
     * @return bool|mixed
     */
    public function parseData($apiResponse)
    {
        $inputData = $apiResponse->data->repositoryOwner->repositories->edges;
        $noDuplication = array();

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
            array_push($this->resultArray, $result);
        }
        return true;
    }
}
