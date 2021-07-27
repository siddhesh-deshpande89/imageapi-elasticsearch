<?php

declare(strict_types=1);

namespace IMS\ImageSearch\Infrastructure;

use IMS\ImageSearch\Application\Service\ImageSearchServiceInterface;
use IMS\ImageSearch\Infrastructure\Engine\SearchClient;

class ImageService implements ImageSearchServiceInterface
{
    private SearchClient $searchClient;

    public function __construct(SearchClient $searchClient)
    {
        $this->searchClient = $searchClient;
    }

    /**
     * Search keyword
     *
     * @param string $keyword
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function search(string $keyword)
    {
        return $this->searchClient->searchImage($keyword);
    }
}
