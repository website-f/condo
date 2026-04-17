<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SeoController extends Controller
{
    public function index(): View
    {
        return view('seo.index');
    }

    public function edit(int $listingId): RedirectResponse
    {
        return $this->comingSoonRedirect();
    }

    public function update(int $listingId): RedirectResponse
    {
        return $this->comingSoonRedirect();
    }

    private function comingSoonRedirect(): RedirectResponse
    {
        return redirect()
            ->route('seo.index')
            ->with('success', 'SEO tools are marked as coming soon for now.');
    }
}
