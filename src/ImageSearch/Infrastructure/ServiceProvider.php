<?php

declare(strict_types=1);

namespace IMS\ImageSearch\Infrastructure;

use IMS\ImageSearch\Application\Service\ImageSearchServiceInterface;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ImageSearchServiceInterface::class, ImageService::class);
    }
}
