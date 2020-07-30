<?php


namespace App\Factories;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

abstract class AbstractReport
{
    /**
     * Report's contents
     *
     * @var array
     */
    public $resultArray = array();

    /**
     * @return mixed
     */
    abstract public function getData();

    /**
     * @param $userIdx
     * @return mixed
     */
    abstract public function setData($userIdx);

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
