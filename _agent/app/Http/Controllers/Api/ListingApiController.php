<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ListingApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Listing::query();

        if ($request->filled('username')) {
            $query->where('username', $request->username);
        }
        if ($request->filled('listingtype')) {
            $query->where('listingtype', $request->listingtype);
        }
        if ($request->filled('propertytype')) {
            $query->where('propertytype', $request->propertytype);
        }
        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('propertyname', 'like', '%' . $request->search . '%')
                  ->orWhere('area', 'like', '%' . $request->search . '%');
            });
        }

        $listings = $query->orderBy('createddate', 'desc')
            ->paginate($request->get('per_page', 12));

        return response()->json($listings);
    }

    public function show(int $id): JsonResponse
    {
        $listing = Listing::with('details')->findOrFail($id);
        return response()->json($listing);
    }
}
