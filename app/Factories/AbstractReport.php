<?php


namespace App\Factories;

use App\Repositories\UserRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

abstract class AbstractReport
{
    protected $resultArray = array();
    protected $githubId;
    protected $githubToken;
    protected $blogUrl;

    public function __construct(UserRepository $userRepo, $userIdx)
    {
        $user = $userRepo->get($userIdx);
        $this->githubId = $user->github_id;
        $this->githubToken = $user->access_token;
        $this->blogUrl = $user->blog_url;
    }

    /**
     * @return mixed
     */
    abstract public function getData();

    /**
     * @return mixed
     */
    abstract public function setData();

    /**
     * @param $apiResponse
     * @return mixed
     */
    abstract public function parseData($apiResponse);

    /**
     * @param $token
     * @param $query
     * @param $type
     * @return bool|mixed
     */
    public function callGithubApi($token, $query, $type)
    {
        $endpoint = "https://api.github.com/graphql";
        $client = new Client();
        try {
            $response = $client->request(
                'post',
                $endpoint,
                [
                    'headers' => [
                        'Authorization' => "Bearer {$token}",
                        'Accept' => 'application/json'
                    ],
                    'json' => [
                        'query' => $query,
                    ]
                ]
            )->getBody();

            Log::info('Calling API for' . $type . 'Data Success');
            return json_decode($response);

        } catch (GuzzleException $e) {
            // api 오류 처리
            Log::info("Calling API for" . $type . "Data Fail");
            Log::debug("Calling API Error Message: \n" . $e);
            return false;
        }
    }
}
