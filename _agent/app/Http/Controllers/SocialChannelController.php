<?php

namespace App\Http\Controllers;

use App\Support\FsPosterBridge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SocialChannelController extends Controller
{
    public function __construct(
        private readonly FsPosterBridge $fsPosterBridge,
    )
    {
    }

    public function index()
    {
        $username = Auth::guard('agent')->user()->username;
        $accounts = $this->fsPosterBridge->channelSessionsForAgent($username);
        $channels = $this->fsPosterBridge->channelManagerRecordsForAgent($username);

        $stats = [
            'accounts' => $accounts->count(),
            'channels' => $channels->where('is_deleted', false)->count(),
            'active' => $channels->where('status', true)->where('is_deleted', false)->count(),
            'archived' => $channels->where('is_deleted', true)->count(),
        ];

        return view('social.channels.index', compact('accounts', 'channels', 'stats'));
    }

    public function createAccount()
    {
        return view('social.channels.account-form', [
            'pageTitle' => 'Add Social Account',
            'formAction' => route('social.accounts.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Save Account',
            'account' => [
                'name' => old('name', ''),
                'social_network' => old('social_network', 'fb'),
                'remote_id' => old('remote_id', ''),
                'method' => old('method', 'app'),
                'proxy' => old('proxy', ''),
                'data_json' => old('data_json', "{\n  \"auth_data\": {}\n}"),
            ],
            'networkOptions' => $this->socialNetworkOptions(),
            'methodOptions' => $this->methodOptions(),
        ]);
    }

    public function storeAccount(Request $request)
    {
        $username = Auth::guard('agent')->user()->username;
        $validated = $this->validateAccount($request);
        $this->fsPosterBridge->createChannelSession($username, $validated);

        return redirect()
            ->route('social.channels.index')
            ->with('success', 'The FS Poster account record was saved. You can now add channels under it from Laravel.');
    }

    public function editAccount(int $sessionId)
    {
        $username = Auth::guard('agent')->user()->username;
        $account = $this->fsPosterBridge->findChannelSessionForAgent($username, $sessionId);

        return view('social.channels.account-form', [
            'pageTitle' => 'Edit Social Account',
            'formAction' => route('social.accounts.update', $sessionId),
            'formMethod' => 'PUT',
            'submitLabel' => 'Update Account',
            'account' => [
                'name' => old('name', $account['name']),
                'social_network' => old('social_network', $account['social_network']),
                'remote_id' => old('remote_id', $account['remote_id']),
                'method' => old('method', $account['method']),
                'proxy' => old('proxy', $account['proxy']),
                'data_json' => old('data_json', $account['data_json']),
            ],
            'networkOptions' => $this->socialNetworkOptions(),
            'methodOptions' => $this->methodOptions(),
        ]);
    }

    public function updateAccount(Request $request, int $sessionId)
    {
        $username = Auth::guard('agent')->user()->username;
        $validated = $this->validateAccount($request);
        $this->fsPosterBridge->updateChannelSession($username, $sessionId, $validated);

        return redirect()
            ->route('social.channels.index')
            ->with('success', 'The FS Poster account record was updated.');
    }

    public function createChannel(Request $request)
    {
        $username = Auth::guard('agent')->user()->username;
        $accounts = $this->fsPosterBridge->channelSessionsForAgent($username);
        $selectedSessionId = (int) $request->query('session', 0);
        $selectedSessionId = $accounts->firstWhere('id', $selectedSessionId)['id'] ?? ($accounts->first()['id'] ?? 0);

        if ($accounts->isEmpty()) {
            return redirect()
                ->route('social.accounts.create')
                ->withErrors([
                    'channel_session_id' => 'Add an FS Poster account first. Channels are created under an existing connected account session.',
                ]);
        }

        return view('social.channels.channel-form', [
            'pageTitle' => 'Add Channel',
            'formAction' => route('social.channels.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Save Channel',
            'channel' => [
                'channel_session_id' => old('channel_session_id', $selectedSessionId),
                'name' => old('name', ''),
                'channel_type' => old('channel_type', ''),
                'remote_id' => old('remote_id', ''),
                'picture' => old('picture', ''),
                'status' => old('status', '1'),
                'auto_share' => old('auto_share', '1'),
                'proxy' => old('proxy', ''),
                'data_json' => old('data_json', "[]"),
                'custom_settings_json' => old('custom_settings_json', "{\n  \"post_filter_type\": \"all\",\n  \"post_filter_terms\": [],\n  \"custom_post_data\": {}\n}"),
            ],
            'accounts' => $accounts,
            'readonlySession' => null,
        ]);
    }

    public function storeChannel(Request $request)
    {
        $username = Auth::guard('agent')->user()->username;
        $validated = $this->validateChannel($request, false);
        $this->fsPosterBridge->createChannel($username, $validated);

        return redirect()
            ->route('social.channels.index')
            ->with('success', 'The FS Poster channel is now available in the Laravel scheduler and the WordPress plugin.');
    }

    public function editChannel(int $channelId)
    {
        $username = Auth::guard('agent')->user()->username;
        $channel = $this->fsPosterBridge->findChannelForAgent($username, $channelId);
        $accounts = $this->fsPosterBridge->channelSessionsForAgent($username);
        $readonlySession = $accounts->firstWhere('id', $channel['channel_session_id']);

        return view('social.channels.channel-form', [
            'pageTitle' => 'Edit Channel',
            'formAction' => route('social.channels.update', $channelId),
            'formMethod' => 'PUT',
            'submitLabel' => 'Update Channel',
            'channel' => [
                'channel_session_id' => old('channel_session_id', $channel['channel_session_id']),
                'name' => old('name', $channel['name']),
                'channel_type' => old('channel_type', $channel['channel_type']),
                'remote_id' => old('remote_id', $channel['remote_id']),
                'picture' => old('picture', $channel['picture']),
                'status' => old('status', $channel['status'] ? '1' : '0'),
                'auto_share' => old('auto_share', $channel['auto_share'] ? '1' : '0'),
                'proxy' => old('proxy', $channel['proxy']),
                'data_json' => old('data_json', $channel['data_json']),
                'custom_settings_json' => old('custom_settings_json', $channel['custom_settings_json']),
            ],
            'accounts' => $accounts,
            'readonlySession' => is_array($readonlySession) ? $readonlySession : null,
        ]);
    }

    public function updateChannel(Request $request, int $channelId)
    {
        $username = Auth::guard('agent')->user()->username;
        $validated = $this->validateChannel($request, true);
        $this->fsPosterBridge->updateChannel($username, $channelId, $validated);

        return redirect()
            ->route('social.channels.index')
            ->with('success', 'The FS Poster channel was updated.');
    }

    public function destroyChannel(int $channelId)
    {
        $username = Auth::guard('agent')->user()->username;
        $this->fsPosterBridge->deleteChannel($username, $channelId);

        return redirect()
            ->route('social.channels.index')
            ->with('success', 'The FS Poster channel was removed from the agent portal.');
    }

    private function validateAccount(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:500'],
            'social_network' => ['required', 'string', 'max:50'],
            'remote_id' => ['required', 'string', 'max:100'],
            'method' => ['required', 'string', 'max:100'],
            'proxy' => ['nullable', 'string', 'max:500'],
            'data_json' => ['required', 'string'],
        ]);

        $this->assertValidJson((string) $validated['data_json'], 'data_json');

        return $validated;
    }

    private function validateChannel(Request $request, bool $editing): array
    {
        $validated = $request->validate([
            'channel_session_id' => [$editing ? 'nullable' : 'required', 'integer'],
            'name' => ['required', 'string', 'max:200'],
            'channel_type' => ['required', 'string', 'max:30'],
            'remote_id' => ['required', 'string', 'max:100'],
            'picture' => ['nullable', 'string', 'max:5000'],
            'proxy' => ['nullable', 'string', 'max:500'],
            'data_json' => ['nullable', 'string'],
            'custom_settings_json' => ['nullable', 'string'],
            'status' => ['nullable', 'in:0,1'],
            'auto_share' => ['nullable', 'in:0,1'],
        ]);

        $this->assertValidJson((string) ($validated['data_json'] ?? '[]'), 'data_json');
        $this->assertValidJson((string) ($validated['custom_settings_json'] ?? '{}'), 'custom_settings_json');

        return $validated;
    }

    private function assertValidJson(string $value, string $field): void
    {
        $value = trim($value);

        if ($value === '') {
            return;
        }

        json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ValidationException::withMessages([
                $field => 'Enter valid JSON so the saved FS Poster data stays usable.',
            ]);
        }
    }

    /**
     * @return array<int, array{key:string,label:string}>
     */
    private function socialNetworkOptions(): array
    {
        return [
            ['key' => 'fb', 'label' => 'Facebook'],
            ['key' => 'instagram', 'label' => 'Instagram'],
            ['key' => 'linkedin', 'label' => 'LinkedIn'],
            ['key' => 'threads', 'label' => 'Threads'],
            ['key' => 'google_b', 'label' => 'Google Business'],
            ['key' => 'tiktok', 'label' => 'TikTok'],
            ['key' => 'pinterest', 'label' => 'Pinterest'],
            ['key' => 'tumblr', 'label' => 'Tumblr'],
            ['key' => 'blogger', 'label' => 'Blogger'],
            ['key' => 'reddit', 'label' => 'Reddit'],
            ['key' => 'twitter', 'label' => 'Twitter / X'],
            ['key' => 'youtube', 'label' => 'YouTube'],
            ['key' => 'medium', 'label' => 'Medium'],
            ['key' => 'vk', 'label' => 'VK'],
            ['key' => 'plurk', 'label' => 'Plurk'],
            ['key' => 'ok', 'label' => 'Odnoklassniki'],
            ['key' => 'truthsocial', 'label' => 'Truth Social'],
        ];
    }

    /**
     * @return array<int, array{key:string,label:string}>
     */
    private function methodOptions(): array
    {
        return [
            ['key' => 'app', 'label' => 'App / OAuth'],
            ['key' => 'cookie', 'label' => 'Cookie / Session'],
            ['key' => 'manual', 'label' => 'Manual import'],
        ];
    }
}
