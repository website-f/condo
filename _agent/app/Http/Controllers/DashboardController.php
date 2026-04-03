<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Listing;
use App\Models\SocialPost;
use App\Models\NewsUpdate;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $agent = Auth::guard('agent')->user();
        $username = $agent->username;

        $totalListings = Listing::where('username', $username)->count();
        $totalArticles = Article::where('agent_username', $username)->count();
        $publishedArticles = Article::where('agent_username', $username)->where('status', 'published')->count();
        $draftArticles = Article::where('agent_username', $username)->where('status', 'draft')->count();
        $scheduledPosts = SocialPost::where('agent_username', $username)->where('status', 'scheduled')->count();
        $publishedPosts = SocialPost::where('agent_username', $username)->where('status', 'published')->count();

        $recentArticles = Article::where('agent_username', $username)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentListings = Listing::where('username', $username)
            ->orderBy('createddate', 'desc')
            ->take(5)
            ->get();

        $upcomingPosts = SocialPost::where('agent_username', $username)
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at', 'asc')
            ->take(5)
            ->get();

        $recentNews = NewsUpdate::published()
            ->orderBy('post_date', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'agent', 'totalListings', 'totalArticles', 'publishedArticles',
            'draftArticles', 'scheduledPosts', 'publishedPosts',
            'recentArticles', 'recentListings', 'upcomingPosts', 'recentNews'
        ));
    }
}
