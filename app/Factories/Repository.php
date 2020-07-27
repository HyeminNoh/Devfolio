<?php

namespace App\Factories;

use App\User;
use Illuminate\Support\Facades\Log;

class Repository extends AbstractReport
{
    private $resultArray = [];

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
                    user(login: "' . $user->github_id . '") {
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
        $apiResponse = $this->callApi($user->access_token, $query, 'Repository');
        $this->parseData($apiResponse);
    }

    public function parseData($apiResponse)
    {
        $this->resultArray = $apiResponse->data->user->pinnedItems;
    }
}
