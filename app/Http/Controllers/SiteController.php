<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SiteController extends Controller
{
    public function home(): View
    {
        return view('pages.home', $this->viewData('Home'));
    }

    public function about(): View
    {
        return view('pages.about', $this->viewData('About Us'));
    }

    public function services(): View
    {
        return view('pages.services', $this->viewData('Services'));
    }

    public function industries(): View
    {
        return view('pages.industries', $this->viewData('Industries'));
    }

    public function insights(): View
    {
        return view('pages.insights', $this->viewData('Insights'));
    }

    public function contact(): View
    {
        return view('pages.contact', $this->viewData('Contact'));
    }

    private function viewData(string $title): array
    {
        return [
            'metaTitle' => $title . ' | ' . config('colldett.company.name'),
            'metaDescription' => config('colldett.company.description'),
            'site' => config('colldett'),
        ];
    }
}
