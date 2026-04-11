<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\SocialPost;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $agent = Auth::guard('agent')->user();
        $username = $agent->username;

        $totalListings = Listing::where('username', $username)->count();
        $scheduledPosts = SocialPost::where('agent_username', $username)->where('status', 'scheduled')->count();
        $publishedPosts = SocialPost::where('agent_username', $username)->where('status', 'published')->count();

        $recentListings = Listing::where('username', $username)
            ->orderBy('createddate', 'desc')
            ->take(5)
            ->get();

        $upcomingPosts = SocialPost::where('agent_username', $username)
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at', 'asc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'agent',
            'totalListings',
            'scheduledPosts',
            'publishedPosts',
            'recentListings',
            'upcomingPosts'
        ));
    }
}
