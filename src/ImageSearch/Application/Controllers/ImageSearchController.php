<?php

declare(strict_types=1);

namespace IMS\ImageSearch\Application\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use IMS\ImageSearch\Application\Service\ImageSearchServiceInterface;

class ImageSearchController extends Controller
{
    private ImageSearchServiceInterface $imageSearchService;

    public function __construct(ImageSearchServiceInterface $imageSearchService)
    {
        $this->imageSearchService = $imageSearchService;
    }

    public function index(Request $request)
    {
        $result = $this->imageSearchService->search($request->keyword);

        return response()->json($result);
    }
}
