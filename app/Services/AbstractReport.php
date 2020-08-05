<?php


namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

abstract class AbstractReport
{
    protected $resultArray = array();
    protected $githubId;
    protected $githubToken;
    protected $blogUrl;

    public function __construct($user)
    {
        $this->githubId = $user->github_id;
        $this->githubToken = $user->access_token;
        $this->blogUrl = $user->blog_url;
        $this->setData();
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
    public function callGraphql($token, $query, $type)
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

            Log::info('Calling API for '. $type .' Data Success');
            $response = json_decode($response);

            // 응답은 왔지만 bad response 경우
            if(empty(isset($response->data))){
                Log::info('Bad response - '.$response->message);
                return false;
            }
            return $response;
        } catch (GuzzleException $e) {
            // api 오류 처리
            Log::info("Calling GraphQL for" . $type . "Data Fail");
            Log::error("Calling GraphQL Error Message: \n" . $e);
            return false;
        }
    }
}
