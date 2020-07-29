<?php


namespace App\Factories;


use App\User;
use GuzzleHttp\Client;
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
        if (empty($blog)) {
            // 빈 배열 그대로 둠
            return false;
        }

        $blog = parse_url($blog, PHP_URL_HOST);
        $splitUrl = explode('.', $blog);
        $blogType = $splitUrl[count($splitUrl) - 2];

        // rss 활용해서 다른 github api 콜과 다름
        switch ($blogType) {
            case 'tistory':
                $client = new Client();
                $response = $client->request('GET', 'http://' . $blog . '/rss')
                    ->getBody()->getContents();
                $response = simplexml_load_string($response, 'SimpleXMLElement');
                $this->parseData($response);
                break;
            default:
                Log::info('Undefined Blog type');
                return false;
        }

    }

    public function parseData($data)
    {
        $category = array();
        $data = json_encode($data);
        $posts = $data->channel->item;
        for ($i = 0; $i < 3; $i++) {
            foreach ($posts[$i] as $detail) {
                array_push($category, $detail);
            }
            array_push($this->resultArray, [
                'title' => $posts[$i]->title,
                'link' => $posts[$i]->link,
                'category' => $category,
                'date' => $posts[$i]->pubDate
            ]);
        }
    }
}
