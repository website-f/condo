<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\SocialPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class SocialMediaController extends Controller
{
    public function index()
    {
        $username = Auth::guard('agent')->user()->username;

        $accounts = SocialAccount::where('agent_username', $username)->get();
        $posts = SocialPost::where('agent_username', $username)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $scheduled = SocialPost::where('agent_username', $username)
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return view('social.index', compact('accounts', 'posts', 'scheduled'));
    }

    public function createAccount()
    {
        return view('social.create-account');
    }

    public function storeAccount(Request $request)
    {
        $request->validate([
            'platform' => 'required|in:facebook,twitter,instagram,linkedin',
            'account_name' => 'required|string|max:255',
            'access_token' => 'nullable|string',
            'page_id' => 'nullable|string|max:255',
        ]);

        SocialAccount::create([
            'agent_username' => Auth::guard('agent')->user()->username,
            'platform' => $request->platform,
            'account_name' => $request->account_name,
            'access_token' => $request->access_token,
            'page_id' => $request->page_id,
        ]);

        return redirect()->route('social.index')->with('success', 'Social account connected.');
    }

    public function destroyAccount(SocialAccount $account)
    {
        if ($account->agent_username !== Auth::guard('agent')->user()->username) {
            abort(403);
        }
        $account->delete();
        return redirect()->route('social.index')->with('success', 'Account removed.');
    }

    public function createPost()
    {
        $username = Auth::guard('agent')->user()->username;
        $accounts = SocialAccount::where('agent_username', $username)->where('is_active', true)->get();
        return view('social.create-post', compact('accounts'));
    }

    public function storePost(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
            'platform' => 'required|in:facebook,twitter,instagram,linkedin',
            'social_account_id' => 'nullable|exists:cms_social_accounts,id',
            'media' => 'nullable|image|max:5120',
            'scheduled_at' => 'nullable|date|after:now',
            'action' => 'required|in:draft,schedule,publish_now',
        ]);

        $data = [
            'agent_username' => Auth::guard('agent')->user()->username,
            'content' => $request->content,
            'platform' => $request->platform,
            'social_account_id' => $request->social_account_id,
        ];

        if ($request->hasFile('media')) {
            $data['media_url'] = $request->file('media')->store('social-media', 'public');
        }

        switch ($request->action) {
            case 'draft':
                $data['status'] = 'draft';
                break;
            case 'schedule':
                $data['status'] = 'scheduled';
                $data['scheduled_at'] = $request->scheduled_at;
                break;
            case 'publish_now':
                $data['status'] = 'published';
                $data['published_at'] = now();
                $this->publishToSocialMedia($data);
                break;
        }

        SocialPost::create($data);

        return redirect()->route('social.index')->with('success', 'Post created successfully.');
    }

    public function destroyPost(SocialPost $post)
    {
        if ($post->agent_username !== Auth::guard('agent')->user()->username) {
            abort(403);
        }
        $post->delete();
        return redirect()->route('social.index')->with('success', 'Post deleted.');
    }

    private function publishToSocialMedia(array &$data): void
    {
        if ($data['platform'] === 'facebook' && !empty($data['social_account_id'])) {
            $account = SocialAccount::find($data['social_account_id']);
            if ($account && $account->access_token && $account->page_id) {
                try {
                    $response = Http::post("https://graph.facebook.com/v18.0/{$account->page_id}/feed", [
                        'message' => $data['content'],
                        'access_token' => $account->access_token,
                    ]);

                    if ($response->successful()) {
                        $data['external_post_id'] = $response->json('id');
                    } else {
                        $data['status'] = 'failed';
                        $data['error_message'] = $response->json('error.message', 'Unknown error');
                    }
                } catch (\Exception $e) {
                    $data['status'] = 'failed';
                    $data['error_message'] = $e->getMessage();
                }
            }
        }
    }
}
