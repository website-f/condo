<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ManagedArticle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ManagedArticle::published();

        if ($request->filled('username')) {
            $query->ownedByAgent((string) $request->username);
        }

        if ($request->filled('category')) {
            $query->withCategory((string) $request->category);
        }

        $articles = $query
            ->orderBy('post_date', 'desc')
            ->paginate($request->integer('per_page', 12));

        return response()->json($articles);
    }

    public function show(string $slug): JsonResponse
    {
        $article = ManagedArticle::published()
            ->where('post_name', $slug)
            ->firstOrFail();

        return response()->json($article);
    }
}
