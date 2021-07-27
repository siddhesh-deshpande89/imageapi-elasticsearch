<?php

declare(strict_types=1);

namespace IMS\ImageSearch\Infrastructure\Elastic;

use Illuminate\Support\Facades\Log;
use Elasticsearch\ClientBuilder;

class ElasticSearchClient
{
    private $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()->setElasticCloudId(config('elasticsearch.cloud_id'))
            ->setBasicAuthentication(config('elasticsearch.username'), config('elasticsearch.password'))
            ->build();
    }

    /**
     * Set index in elasticsearch
     *
     * @param array $imageResult
     * @return bool
     */
    public function setIndex(array $imageResult): bool
    {
        try {

            // Set Index
            $params = [
                'index' => 'image_search',
                'id' => 'keyword_id_' . hash('sha256', $imageResult['keyword']),
                'body' => [
                    'script' => [
                        'source' => 'ctx._source.keyword=params.keyword;ctx._source.images=params.images',
                        'params' => [
                            'keyword' => $imageResult['keyword'],
                            'images' => (object)$imageResult['images']
                        ]
                    ],
                    'upsert' => [
                        'keyword' => $imageResult['keyword'],
                        'images' => (object)$imageResult['images']
                    ]
                ]
            ];

            $this->client->update($params);
            return true;
        } catch (\Exception $ex) {
            Log::channel('elasticsearch')->error('Error occured in set index', [
                'message' => $ex->getMessage(),
                'line' => $ex->getLine()
            ]);
            return false;
        }
    }

    /**
     * Sets indexes in bulk
     *
     * @param array $imageResults
     * @return array|callable
     */
    public function setBulkIndex(array $imageResults)
    {
        $params = [];
        foreach ($imageResults as $imageResult) {
            $params['body'][] = [
                'index' => [
                    '_index' => 'image_search'
                ]
            ];

            $params['body'][] = [
                'keyword' => $imageResult['keyword'],
                'images' => $imageResult['images']
            ];
        }

        return $this->client->index($params);
    }

    /**
     * Search Multiple keywords
     *
     * @param array $keywords
     * @return array
     */
    public function bulkSearchKeywords(array $keywords): array
    {
        $search = [];
        foreach ($keywords as $keyword) {
            $search[] = [
                'wildcard' => [
                    'keyword' => "*$keyword*"
                ]
            ];
        }

        $params = [
            'index' => 'image_search',
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => $search
                    ]
                ]
            ]
        ];

        try {
            return $this->client->search($params);
        } catch (\Exception $ex) {
            Log::channel('image_search')->error('Error searching words', [
                'keywords' => $keywords,
                'message' => $ex->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Maps Keywords to returned image results from elastic search
     *
     * @param array $keywords
     * @param array $results
     * @return array
     */
    public function mapKeywordImages(array $keywords, array $results): array
    {
        $final = [];
        if (!empty($results['hits']['hits'])) {
            $items = $results['hits']['hits'];

            foreach ($keywords as $keyword) {
                foreach ($items as $item) {
                    if (strpos($item['_source']['keyword'], $keyword) !== false) {
                        $final[] = [
                            'keyword' => $keyword,
                            'images' => $item['_source']['images']
                        ];
                        continue 2;
                    }
                }
            }
        }
        return $final;
    }
}
