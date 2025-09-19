<?php

namespace App\Interfaces;

interface CmsInterface
{
    public function getPages();

    public function getPageBySlug(string $slug);
}
