<?php

namespace App\Factories;

use App\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class Repository extends AbstractReport
{
    private $token;
    private $userId;

    /**
     * Repository constructor.
     * @param $userIdx
     */
    public function __construct($userIdx)
    {
        $user = User::find($userIdx);
        $this->userId = $user->github_id;
        $this->token = $user->access_token;
        $this->setData($userIdx);
    }

    /**
     * Return repository instance value
     *
     * @return false|mixed|string
     */
    public function getData()
    {
        return json_encode($this->resultArray);
    }

    /**
     * Fill repository instance value
     *
     * @param $userIdx
     * @return mixed|void
     */
    public function setData($userIdx)
    {
        $query = 'query {
                    user(login: "' . $this->userId . '") {
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
        $apiResponse = $this->callGithubApi($this->token, $query, 'Repository');
        $this->parseData($apiResponse);
    }

    /**
     * Get repository detail information from github rest api
     *
     * @param $repoNameWithOwner
     * @param $token
     * @return bool|\Psr\Http\Message\StreamInterface
     */
    public function getAdditionalData($repoNameWithOwner, $token)
    {
        $client = new Client();
        try {
            $response = $client->request('GET', 'https://api.github.com/repos/' . $repoNameWithOwner . '/contributors',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/json'
                    ]
                ])->getBody();
            Log::info("Calling " . $repoNameWithOwner . " data is Success");
            return $response;
        } catch (GuzzleException $e) {
            // api 오류 처리
            Log::info("Calling" . $repoNameWithOwner . "data is Fail");
            Log::debug("Calling API Error Message: \n" . $e);
            return false;
        }

    }

    /**
     * Parse repository information from github graphql response
     *
     * @param $apiResponse
     * @return mixed|void
     */
    public function parseData($apiResponse)
    {
        $repositories = $apiResponse->data->user->pinnedItems->edges;
        foreach ($repositories as $repo) {
            $repoData = $repo->node;
            $contributor = $this->getAdditionalData($repoData->nameWithOwner, $this->token);
            $repoArray = array(
                [
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
                ]
            );
            array_push($this->resultArray, $repoArray[0]);
        }
    }
}
