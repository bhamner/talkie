<?php

use App\Services\BoardTemplateService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(BoardTemplateService::class)->backfillMissingIconsFromCatalog();
    }
};
