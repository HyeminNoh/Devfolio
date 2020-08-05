<?php


namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class Blog extends AbstractReport
{
    /**
     * Return blog instance value
     *
     * @return false|mixed|string
     */
    public function getData()
    {
        return json_encode($this->resultArray);
    }

    /**
     * Fill blog instance value
     *
     * @return bool|mixed
     */
    public function setData()
    {
        if (empty($this->blogUrl)) {
            return false;
        }
        $blogHost = parse_url($this->blogUrl, PHP_URL_HOST);
        $splitUrl = explode('.', $blogHost);
        $blogType = $splitUrl[count($splitUrl) - 2];

        // rss 활용해서 다른 github api 콜과 다름
        switch ($blogType) {
            case 'tistory':
                $requestUrl = 'http://'.$blogHost.'/rss';
                $this->getRssFeed($requestUrl);
                break;
            case 'naver':
                $blogPath = parse_url($this->blogUrl, PHP_URL_PATH);
                $requestUrl = 'http://rss.'.$blogHost.$blogPath.'.xml';
                $this->getRssFeed($requestUrl);
                break;
            default:
                Log::info('Undefined Blog type');
        }
        return true;
    }

    /**
     * Parse rss feed data
     *
     * @param $data
     * @return mixed|void
     */
    public function parseData($data)
    {
        if(!isset($data[0]->channel->item)){
            Log::info('Blog post not exist');
            return false;
        }
        $posts = $data[0]->channel->item;
        $postSize = count($posts);

        for ($i = 0; $i < $postSize; $i++) {
            // 블로그 포스팅 개수는 3개까지
            if($i>=3){
                break;
            }
            $postArray = json_decode(json_encode($posts[$i]));
            array_push($this->resultArray, [
                'title' => $postArray->title,
                'link' => $postArray->link,
                'category' => $postArray->category,
                'date' => strftime("%Y-%m-%d", strtotime($postArray->pubDate))
            ]);
        }
        return true;
    }

    public function getRssFeed($requestUrl){
        $client = new Client();
        try {
            $response = $client->request('GET', $requestUrl, [
                'header' => [
                    'Accept' => 'application/xml'
                ]
            ])
                ->getBody()->getContents();
            $response = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
            return $this->parseData($response);
        } catch (GuzzleException $e) {
            Log::info('Loading rss blog data is fail');
            Log::error("Loading rss blog data error message: \n".$e);
            return false;
        }
    }
}
