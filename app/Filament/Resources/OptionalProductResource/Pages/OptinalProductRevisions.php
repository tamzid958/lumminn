<?php

namespace App\Filament\Resources\OptionalProductResource\Pages;

use App\Filament\Resources\OptionalProductResource;
use App\Filament\Resources\ProductResource;
use Mansoor\FilamentVersionable\RevisionsPage;

class OptinalProductRevisions extends RevisionsPage
{
    protected static string $resource = OptionalProductResource::class;
}
