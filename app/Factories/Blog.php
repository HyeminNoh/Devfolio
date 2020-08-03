<?php


namespace App\Factories;

use App\Repositories\UserRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class Blog extends AbstractReport
{
    /**
     * Blog constructor.
     * @param $userIdx
     */
    public function __construct($userIdx)
    {
        AbstractReport::__construct(new UserRepository(), $userIdx);
        $this->setData();
    }

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
        $posts = json_encode($data[0]->channel);
        //Log::info(json_encode(json_decode($posts)->item));
        $posts = json_decode($posts)->item;
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
            $this->parseData($response);
            return true;
        } catch (GuzzleException $e) {
            Log::info('Loading rss blog data is fail');
            Log::error("Loading rss blog data error message: \n".$e);
            return false;
        }
    }
}
