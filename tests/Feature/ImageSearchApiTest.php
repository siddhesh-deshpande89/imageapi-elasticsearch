<?php
declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Http\Response;
use Tests\TestCase;

class ImageSearchApiTest extends TestCase
{
    private string $endpointUrl;

    public function setUp(): void
    {
        parent::setUp();
        $this->endpointUrl = '/api/imagesearch';
    }

    /**
     * @dataProvider provideValidKeyword
     * @test
     * @param string $keyword
     */
    public function shouldReturnImageSearchDataCorrectly(string $keyword)
    {
        $response = $this->json('GET', $this->endpointUrl, ['keyword' => $keyword]);

        $response->assertStatus(Response::HTTP_OK);
    }

    public function provideValidKeyword()
    {
        return [
            [
                'basketball',
                'football',
                'tower of pisa'
            ]
        ];
    }
}