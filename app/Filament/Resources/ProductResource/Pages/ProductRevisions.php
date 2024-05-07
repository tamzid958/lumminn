<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Mansoor\FilamentVersionable\RevisionsPage;

class ProductRevisions extends RevisionsPage
{
    protected static string $resource = ProductResource::class;
}
