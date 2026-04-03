<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ArticleApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Article::published();

        if ($request->filled('username')) {
            $query->where('agent_username', $request->username);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $articles = $query->orderBy('published_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return response()->json($articles);
    }

    public function show(string $slug): JsonResponse
    {
        $article = Article::published()->where('slug', $slug)->firstOrFail();
        return response()->json($article);
    }
}
