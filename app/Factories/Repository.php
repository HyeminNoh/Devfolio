<?php

namespace App\Factories;

use App\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class Repository extends AbstractReport
{
    private $resultArray = array();
    private $token;
    private $userId;
    public function __construct($userIdx)
    {
        $user = User::find($userIdx);
        $this->userId = $user->github_id;
        $this->token = $user->access_token;
        $this->setData($userIdx);
    }

    public function getData()
    {
        return json_encode($this->resultArray);
    }

    public function setData($userIdx)
    {
        $query = 'query {
                    user(login: "'.$this->userId.'") {
                        email
                        pinnedItems(first: 6, types: [REPOSITORY]) {
                            totalCount
                            edges {
                              node {
                                ... on Repository {
                                  name
                                  nameWithOwner
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
                      }';
        $apiResponse = $this->callApi($this->token, $query, 'Repository');
        $this->parseData($apiResponse);
    }

    public function getAdditionalData($repoNameWithOwner, $token){
        $client = new Client();
        try {
            $response = $client->request('GET', 'https://api.github.com/repos/'.$repoNameWithOwner . '/contributors', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json'
                ]
            ])->getBody();
            Log::info("Calling ".$repoNameWithOwner." data is Success");
            return $response;
        } catch (GuzzleException $e) {
            // api 오류 처리
            Log::info("Calling".$repoNameWithOwner."data is Fail");
            Log::debug("Calling API Error Message: \n" . $e);
            return false;
        }

    }
    public function parseData($apiResponse)
    {
        $repositories = $apiResponse->data->user->pinnedItems->edges;
        foreach ($repositories as $repo) {
            $repoData = $repo->node;
            $contributor = $this->getAdditionalData($repoData->nameWithOwner, $this->token);
            // Log::info($contributor);
            $repoArray = array([
                'name' => $repoData->name,
                'description' => $repoData->description,
                'forkCount' => $repoData->forkCount,
                'totalCount' => $repoData->stargazers->totalCount,
                'url' => $repoData->url,
                'homepageUrl' => $repoData->homepageUrl,
                'diskUsage' => $repoData->diskUsage,
                'primaryLanguage' => $repoData->primaryLanguage,
                'languages' => $repoData->languages,
                'contributor' => json_decode($contributor)
            ]);
            array_push($this->resultArray, $repoArray[0]);
        }
    }
}
