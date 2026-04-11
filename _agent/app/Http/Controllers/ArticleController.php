<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(): View
    {
        return $this->comingSoonView();
    }

    public function create(): View
    {
        return $this->comingSoonView();
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->comingSoonRedirect();
    }

    public function edit(Article $article): View
    {
        return $this->comingSoonView();
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        return $this->comingSoonRedirect();
    }

    public function destroy(Article $article): RedirectResponse
    {
        return $this->comingSoonRedirect();
    }

    private function comingSoonView(): View
    {
        return view('articles.coming-soon');
    }

    private function comingSoonRedirect(): RedirectResponse
    {
        return redirect()
            ->route('articles.index')
            ->with('success', 'Articles are coming soon.');
    }
}
