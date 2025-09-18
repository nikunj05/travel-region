<?php

namespace App\Repositories;

use App\Interfaces\CmsInterface;
use App\Models\CmsPage;

class CmsRepository implements CmsInterface
{
    /**
     * Get CMS content by type.
     *
     * @param string $type
     * @return CmsPage|null
     */
    public function getContentByType(string $type)
    {
        return CmsPage::where('type', $type)->first();
    }
}
