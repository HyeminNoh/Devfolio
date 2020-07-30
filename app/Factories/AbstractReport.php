<?php


namespace App\Factories;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

abstract class AbstractReport
{
    abstract public function getData();

    abstract public function setData($userIdx);

    abstract public function parseData($apiResponse);

    public function callApi($token, $query, $type)
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
