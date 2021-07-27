<?php

declare(strict_types=1);

namespace IMS\ImageSearch\Infrastructure\Engine;

use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use IMS\ImageSearch\Infrastructure\Elastic\ElasticSearchClient;

class SearchClient
{
    private string $apiEndpoint;

    private string $accessKey;

    private string $maxImagePerWord;

    private ElasticSearchClient $elasticSearchClient;

    public function __construct(ElasticSearchClient $elasticSearchClient)
    {
        $this->apiEndpoint = config('bing.endpoint_url');
        $this->accessKey = config('bing.subscription_key');
        $this->maxImagePerWord = config('bing.max_words');

        $this->elasticSearchClient = $elasticSearchClient;
    }

    /**
     * Returns keyword and related images
     *
     * @param string $keyword
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function searchImage(string $keyword): array
    {
        // Prevent calling bing api for same words
        $images = Cache::get('keyword_' . $keyword);

        if (empty($images)) {
            $images = $this->requestBingApi($keyword);
            Cache::put('keyword_' . $keyword, $images);
        }

        return $images;
    }

    /**
     * Calls Bing Api
     *
     * @param string $keyword
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function requestBingApi(string $keyword): array
    {
        $params = [
            'headers' => [
                'Ocp-Apim-Subscription-Key' => $this->accessKey
            ],
            'query' => [
                'q' => urlencode($keyword)
            ]
        ];

        $images = [];

        try {
            $client = new Client();
            $response = $client->request('GET', $this->apiEndpoint, $params);

            if ($response->getStatusCode() == Response::HTTP_OK) {
                $contents = json_decode($response->getBody()->getContents());

                $i = 0;

                while ($i < $this->maxImagePerWord) {
                    $images[] = $contents->value[$i]->contentUrl;
                    $i ++;

                    $this->elasticSearchClient->setIndex($images);
                }

                Log::error('Successfully called bing image search api', [
                    'keyword' => $keyword
                ]);

                return $images;
            }
        } catch (\Exception $ex) {
            Log::error('Error occured during bing image search api', [
                'keyword' => $keyword,
                'message' => $ex->getMessage(),
                'line' => $ex->getLine()
            ]);
            return $images;
        }
    }
}
