<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsUpdate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NewsApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $news = NewsUpdate::published()
            ->orderBy('post_date', 'desc')
            ->paginate($request->get('per_page', 12));

        return response()->json($news);
    }

    public function show(int $id): JsonResponse
    {
        $article = NewsUpdate::published()->findOrFail($id);
        return response()->json($article);
    }
}
