<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\StreamInterface;

class Repository extends AbstractReport
{
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
     * @return mixed|void
     */
    public function setData()
    {
        $query = 'query {
                    user(login: "' . $this->githubId . '") {
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
        $apiResponse = $this->callGraphql($this->githubToken, $query, 'Repository');
        if(empty($apiResponse)){
            Log::info('Set Repository instance data is fail');
            return false;
        }
        return $this->parseData($apiResponse);
    }

    /**
     * Get repository detail information from github rest api
     *
     * @param $repoNameWithOwner
     * @param $token
     * @return bool|StreamInterface
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
            Log::info("Calling" . $repoNameWithOwner . " data is Fail");
            Log::error("Calling Rest API Error Message: \n" . $e);
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

        if (empty($repositories)) {
            Log::info('Repository Data for parsing is empty');
            return false;
        }

        foreach ($repositories as $repo) {
            $repoData = $repo->node;
            $contributor = $this->getAdditionalData($repoData->nameWithOwner, $this->githubToken);
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
        return true;
    }
}
