<?php

namespace App\Factories;

use App\User;

class Skill extends AbstractReport
{
    private $resultArray = array();

    public function __construct($userIdx)
    {
        $this->setData($userIdx);
    }

    public function getData()
    {
        return json_encode($this->resultArray);
    }

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
        $apiResponse = $this->callApi($user->access_token, $query, 'Skill');
        $this->parseData($apiResponse);
    }

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
    }
}
