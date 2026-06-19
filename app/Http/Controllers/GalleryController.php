<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use Illuminate\View\View;

class GalleryController extends SiteController
{
    public function index(): View
    {
        $data = $this->viewData('Gallery');
        $data['metaDescription'] = 'Field work, engagements, and operational highlights from Colldett Trace Limited.';
        $data['canonicalUrl'] = route('gallery', absolute: true);
        $data['ogImageAlt'] = 'Gallery — '.$data['site']['company']['name'];
        $data['items'] = GalleryItem::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get(['image_path', 'caption']);

        return view('pages.gallery', $data);
    }
}
