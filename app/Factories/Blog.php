<?php


namespace App\Factories;


use App\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class Blog extends AbstractReport
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
        $blog = $user->blog_url;

        if(empty($blog)){
            return false;
        }
        $blog = parse_url($blog, PHP_URL_HOST);
        $splitUrl = explode('.', $blog);
        $blogType = $splitUrl[count($splitUrl) - 2];

        // rss 활용해서 다른 github api 콜과 다름
        switch ($blogType) {
            case 'tistory':
                $client = new Client();
                try {
                    $response = $client->request('GET', 'http://' . $blog . '/rss')
                        ->getBody()->getContents();
                    $response = simplexml_load_string($response, 'SimpleXMLElement');
                    $this->parseData($response);
                } catch (GuzzleException $e) {
                    Log::info('Loading rss blog data is fail');
                }
                break;
            default:
                Log::info('Undefined Blog type');
        }
        return true;
    }

    public function parseData($data)
    {
        $posts = json_encode($data[0]->channel);
        // Log::info(json_encode(json_decode($posts)->item));
        $posts = json_decode($posts)->item;
        for($i=0; $i<3; $i++){
            $postArray = json_decode(json_encode($posts[$i]));
            array_push($this->resultArray, [
                'title' => $postArray->title,
                'link' => $postArray->link,
                'category' => $postArray->category,
                'date' => strftime("%Y-%m-%d", strtotime($postArray->pubDate))
            ]);
        }
    }
}
