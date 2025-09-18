<?php

namespace App\Interfaces;

interface CmsInterface
{
    public function getContentByType(string $type);
}
