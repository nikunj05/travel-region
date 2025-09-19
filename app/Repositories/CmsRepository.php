<?php

namespace App\Repositories;

use App\Interfaces\CmsInterface;
use App\Models\CmsPage;

class CmsRepository implements CmsInterface
{
    /**
     * Get all CMS pages.
     *
     * @return CmsPage[]|null
     */
    public function getPages()
    {
        return CmsPage::all();
    }

    /**
     * Get a CMS page by its slug.
     *
     * @param  string  $slug
     * @return CmsPage|null
     */
    public function getPageBySlug(string $slug)
    {
        return CmsPage::where('slug', $slug)->first();
    }
}
