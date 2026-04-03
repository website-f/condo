<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Listing;
use App\Models\SocialPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $username = Auth::guard('agent')->user()->username;
        $period = $request->get('period', '30');

        $startDate = now()->subDays((int) $period);

        // Listing stats
        $totalListings = Listing::where('username', $username)->count();
        $listingsByType = Listing::where('username', $username)
            ->select('listingtype', DB::raw('count(*) as total'))
            ->groupBy('listingtype')
            ->get();
        $listingsByPropertyType = Listing::where('username', $username)
            ->select('propertytype', DB::raw('count(*) as total'))
            ->groupBy('propertytype')
            ->get();
        $listingsByState = Listing::where('username', $username)
            ->select('state', DB::raw('count(*) as total'))
            ->groupBy('state')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();

        // Article stats
        $totalArticles = Article::where('agent_username', $username)->count();
        $articlesByStatus = Article::where('agent_username', $username)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // Social media stats
        $totalSocialPosts = SocialPost::where('agent_username', $username)->count();
        $socialByPlatform = SocialPost::where('agent_username', $username)
            ->select('platform', DB::raw('count(*) as total'))
            ->groupBy('platform')
            ->get();
        $socialByStatus = SocialPost::where('agent_username', $username)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        return view('reports.index', compact(
            'totalListings', 'listingsByType', 'listingsByPropertyType', 'listingsByState',
            'totalArticles', 'articlesByStatus',
            'totalSocialPosts', 'socialByPlatform', 'socialByStatus', 'period'
        ));
    }
}
