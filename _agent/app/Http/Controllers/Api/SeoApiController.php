<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SeoApiController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'page_type' => 'required|string',
            'page_identifier' => 'nullable|string',
        ]);

        $query = SeoSetting::where('agent_username', $request->username)
            ->where('page_type', $request->page_type);

        if ($request->filled('page_identifier')) {
            $query->where('page_identifier', $request->page_identifier);
        }

        $seo = $query->first();

        if (!$seo) {
            return response()->json(['message' => 'No SEO settings found'], 404);
        }

        return response()->json($seo);
    }
}
