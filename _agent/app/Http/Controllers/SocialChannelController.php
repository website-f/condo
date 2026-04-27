<?php

namespace App\Http\Controllers;

use App\Support\FsPosterBridge;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SocialChannelController extends Controller
{
    public function __construct(
        private readonly FsPosterBridge $fsPosterBridge,
    )
    {
    }

    public function index(Request $request)
    {
        $username = Auth::guard('agent')->user()->username;
        $allAccounts = $this->fsPosterBridge->channelSessionsForAgent($username);
        $allChannels = $this->fsPosterBridge->channelManagerRecordsForAgent($username);
        $networkOptions = collect($this->socialNetworkOptions());
        $selectedNetwork = trim((string) $request->query('network', ''));
        $search = trim((string) $request->query('search', ''));
        $filter = trim((string) $request->query('filter', 'all'));
        $allowedFilters = ['all', 'connected', 'auto_share', 'disabled', 'archived'];

        if ($selectedNetwork !== '' && ! $networkOptions->pluck('key')->contains($selectedNetwork)) {
            $selectedNetwork = '';
        }

        if (! in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }

        $networkCards = $networkOptions
            ->map(function (array $network) use ($allAccounts, $allChannels) {
                $networkAccounts = $allAccounts->where('social_network', $network['key']);
                $networkChannels = $allChannels->where('social_network', $network['key']);

                return [
                    'key' => $network['key'],
                    'label' => $network['label'],
                    'accounts' => $networkAccounts->count(),
                    'channels' => $networkChannels->where('is_deleted', false)->count(),
                ];
            })
            ->values();

        $channels = $allChannels
            ->filter(function (array $channel) use ($selectedNetwork, $search, $filter) {
                if ($selectedNetwork !== '' && $channel['social_network'] !== $selectedNetwork) {
                    return false;
                }

                $matchesFilter = match ($filter) {
                    'connected' => ! $channel['is_deleted'] && $channel['status'],
                    'auto_share' => ! $channel['is_deleted'] && $channel['auto_share'],
                    'disabled' => ! $channel['is_deleted'] && ! $channel['status'],
                    'archived' => $channel['is_deleted'],
                    default => true,
                };

                if (! $matchesFilter) {
                    return false;
                }

                if ($search === '') {
                    return true;
                }

                $haystack = mb_strtolower(implode(' ', [
                    (string) $channel['name'],
                    (string) $channel['social_network_label'],
                    (string) $channel['session_name'],
                    (string) $channel['channel_type'],
                    (string) $channel['remote_id'],
                ]));

                return str_contains($haystack, mb_strtolower($search));
            })
            ->values();

        $allStats = [
            'accounts' => $allAccounts->count(),
            'channels' => $allChannels->where('is_deleted', false)->count(),
            'active' => $allChannels->where('status', true)->where('is_deleted', false)->count(),
            'archived' => $allChannels->where('is_deleted', true)->count(),
        ];

        $stats = [
            'accounts' => $selectedNetwork !== ''
                ? $allAccounts->where('social_network', $selectedNetwork)->count()
                : $allStats['accounts'],
            'channels' => $channels->where('is_deleted', false)->count(),
            'active' => $channels->where('status', true)->where('is_deleted', false)->count(),
            'archived' => $channels->where('is_deleted', true)->count(),
        ];

        $selectedNetworkLabel = $selectedNetwork !== ''
            ? (string) ($networkOptions->firstWhere('key', $selectedNetwork)['label'] ?? $selectedNetwork)
            : 'All';

        $perPage = 10;
        $currentPage = max(1, (int) $request->query('page', 1));
        $totalChannels = $channels->count();
        $pagedItems = $channels->forPage($currentPage, $perPage)->values();

        $channelsPaginator = new LengthAwarePaginator(
            $pagedItems,
            $totalChannels,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $channels = $pagedItems;
        $popupAuthNetworks = $this->popupAuthNetworks();

        return view('social.channels.index', compact(
            'channels',
            'channelsPaginator',
            'allStats',
            'stats',
            'networkCards',
            'popupAuthNetworks',
            'selectedNetwork',
            'selectedNetworkLabel',
            'search',
            'filter',
        ));
    }

    public function createAccount()
    {
        return view('social.channels.account-form', [
            'pageTitle' => 'Advanced Account',
            'formAction' => route('social.accounts.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Save Account',
            'isCreate' => true,
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
            ->with('success', 'The account was saved and is ready for channels.');
    }

    public function editAccount(int $sessionId)
    {
        $username = Auth::guard('agent')->user()->username;
        $account = $this->fsPosterBridge->findChannelSessionForAgent($username, $sessionId);

        return view('social.channels.account-form', [
            'pageTitle' => 'Edit Account',
            'formAction' => route('social.accounts.update', $sessionId),
            'formMethod' => 'PUT',
            'submitLabel' => 'Save Changes',
            'isCreate' => false,
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
            ->with('success', 'The account was updated.');
    }

    public function createChannel(Request $request)
    {
        $username = Auth::guard('agent')->user()->username;
        $accounts = $this->fsPosterBridge->channelSessionsForAgent($username);
        $allChannels = $this->fsPosterBridge->channelManagerRecordsForAgent($username);
        $networkOptions = collect($this->socialNetworkOptions());
        $selectedNetwork = trim((string) old('social_network', $request->query('network', '')));

        if ($selectedNetwork !== '' && ! $networkOptions->pluck('key')->contains($selectedNetwork)) {
            $selectedNetwork = '';
        }

        $networkAccounts = $selectedNetwork !== ''
            ? $accounts->where('social_network', $selectedNetwork)->values()
            : collect();
        $networkChannels = $selectedNetwork !== ''
            ? $allChannels->where('social_network', $selectedNetwork)->where('is_deleted', false)->values()
            : collect();
        $selectedSessionId = (int) old('channel_session_id', $request->query('session', 0));

        if ($selectedNetwork !== '' && ! $networkAccounts->pluck('id')->contains($selectedSessionId)) {
            $selectedSessionId = (int) ($networkAccounts->first()['id'] ?? 0);
        }

        $sessionMode = old('session_mode', $selectedSessionId > 0 ? 'existing' : 'new');

        if ($networkAccounts->isEmpty()) {
            $sessionMode = 'new';
        }

        return view('social.channels.channel-form', [
            'pageTitle' => 'Add Channel',
            'formAction' => route('social.channels.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Add Channel',
            'isCreate' => true,
            'selectedNetwork' => $selectedNetwork,
            'selectedNetworkLabel' => $selectedNetwork !== ''
                ? (string) ($networkOptions->firstWhere('key', $selectedNetwork)['label'] ?? $selectedNetwork)
                : '',
            'networkOptions' => $networkOptions->all(),
            'networkAccounts' => $networkAccounts,
            'networkChannels' => $networkChannels,
            'channelTypeOptions' => $this->channelTypeOptions($selectedNetwork),
            'methodOptions' => $this->methodOptions(),
            'sessionMode' => $sessionMode,
            'oauthConnectUrl' => $selectedNetwork !== '' && $this->supportsPopupAuth($selectedNetwork)
                ? route('social.channels.oauth-start', ['network' => $selectedNetwork])
                : null,
            'quickConnect' => [
                'social_network' => old('social_network', $selectedNetwork),
                'channel_session_id' => old('channel_session_id', $selectedSessionId > 0 ? (string) $selectedSessionId : ''),
                'account_name' => old('account_name', ''),
                'account_remote_id' => old('account_remote_id', ''),
                'method' => old('method', 'app'),
                'proxy_enabled' => old('proxy_enabled', old('proxy', '') !== '' ? '1' : '0'),
                'proxy' => old('proxy', ''),
            ],
            'channel' => [
                'channel_session_id' => old('channel_session_id', $selectedSessionId > 0 ? (string) $selectedSessionId : ''),
                'name' => old('name', ''),
                'channel_type' => old('channel_type', ''),
                'remote_id' => old('remote_id', ''),
                'picture' => old('picture', ''),
                'status' => old('status', '1'),
                'auto_share' => old('auto_share', '1'),
                'data_json' => old('data_json', ''),
                'custom_settings_json' => old('custom_settings_json', ''),
            ],
            'readonlySession' => null,
        ]);
    }

    public function storeChannel(Request $request)
    {
        $username = Auth::guard('agent')->user()->username;
        $validated = $this->validateChannelWizard($request, false);
        $sessionId = $this->resolveChannelSessionId($username, $validated);

        $this->fsPosterBridge->createChannel($username, [
            'channel_session_id' => $sessionId,
            'name' => $validated['name'],
            'channel_type' => $validated['channel_type'],
            'remote_id' => $validated['remote_id'],
            'picture' => $validated['picture'] ?? '',
            'status' => $validated['status'] ?? '1',
            'auto_share' => $validated['auto_share'] ?? '1',
            'proxy' => ($validated['proxy_enabled'] ?? '0') === '1' ? ($validated['proxy'] ?? '') : '',
            'data_json' => $validated['data_json'] ?? '[]',
            'custom_settings_json' => $validated['custom_settings_json'] ?? '',
        ]);

        return redirect()
            ->route('social.channels.index', array_filter([
                'network' => $validated['social_network'] ?? null,
            ]))
            ->with('success', 'The channel is now ready in the Laravel scheduler.');
    }

    public function editChannel(int $channelId)
    {
        $username = Auth::guard('agent')->user()->username;
        $channel = $this->fsPosterBridge->findChannelForAgent($username, $channelId);
        $accounts = $this->fsPosterBridge->channelSessionsForAgent($username);
        $readonlySession = $accounts->firstWhere('id', $channel['channel_session_id']);
        $networkChannels = $this->fsPosterBridge->channelManagerRecordsForAgent($username)
            ->where('social_network', $channel['social_network'])
            ->where('is_deleted', false)
            ->reject(fn (array $record) => $record['id'] === $channel['id'])
            ->values();

        return view('social.channels.channel-form', [
            'pageTitle' => 'Edit Channel',
            'formAction' => route('social.channels.update', $channelId),
            'formMethod' => 'PUT',
            'submitLabel' => 'Save Changes',
            'isCreate' => false,
            'selectedNetwork' => $channel['social_network'],
            'selectedNetworkLabel' => $channel['social_network_label'],
            'networkOptions' => $this->socialNetworkOptions(),
            'networkAccounts' => $accounts->where('social_network', $channel['social_network'])->values(),
            'networkChannels' => $networkChannels,
            'channelTypeOptions' => $this->channelTypeOptions($channel['social_network']),
            'methodOptions' => $this->methodOptions(),
            'sessionMode' => 'existing',
            'oauthConnectUrl' => null,
            'quickConnect' => [
                'social_network' => $channel['social_network'],
                'channel_session_id' => (string) $channel['channel_session_id'],
                'account_name' => '',
                'account_remote_id' => '',
                'method' => is_array($readonlySession) ? $readonlySession['method'] : 'app',
                'proxy_enabled' => $channel['proxy'] !== '' ? '1' : '0',
                'proxy' => $channel['proxy'],
            ],
            'channel' => [
                'channel_session_id' => old('channel_session_id', (string) $channel['channel_session_id']),
                'name' => old('name', $channel['name']),
                'channel_type' => old('channel_type', $channel['channel_type']),
                'remote_id' => old('remote_id', $channel['remote_id']),
                'picture' => old('picture', $channel['picture']),
                'status' => old('status', $channel['status'] ? '1' : '0'),
                'auto_share' => old('auto_share', $channel['auto_share'] ? '1' : '0'),
                'data_json' => old('data_json', $channel['data_json']),
                'custom_settings_json' => old('custom_settings_json', $channel['custom_settings_json']),
            ],
            'readonlySession' => is_array($readonlySession) ? $readonlySession : null,
        ]);
    }

    public function oauthStart(Request $request, string $network)
    {
        $network = trim($network);

        if (! collect($this->socialNetworkOptions())->pluck('key')->contains($network) || ! $this->supportsPopupAuth($network)) {
            abort(404);
        }

        $proxy = trim((string) $request->query('proxy', ''));
        $appId = $this->fsPosterBridge->preferredOauthAppId($network);
        $username = Auth::guard('agent')->user()->username;

        return redirect()->away($this->fsPosterBridge->wordpressBridgeAuthStartUrl(
            $username,
            $network,
            $appId,
            $proxy !== '' ? $proxy : null
        ));
    }

    public function importOauthChannels(Request $request)
    {
        $validated = $request->validate([
            'social_network' => ['required', 'string', 'max:50'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*.channel_session_id' => ['required', 'integer'],
            'channels.*.channel_type' => ['required', 'string', 'max:30'],
            'channels.*.remote_id' => ['required', 'string', 'max:100'],
            'channels.*.name' => ['nullable', 'string', 'max:200'],
            'channels.*.picture' => ['nullable', 'string', 'max:5000'],
            'channels.*.data' => ['nullable', 'array'],
        ]);

        $socialNetwork = trim((string) $validated['social_network']);

        if (! $this->supportsPopupAuth($socialNetwork)) {
            throw ValidationException::withMessages([
                'social_network' => 'This network still needs the advanced/manual connection flow.',
            ]);
        }

        $username = Auth::guard('agent')->user()->username;
        $sessions = $this->fsPosterBridge->channelSessionsForAgent($username)->keyBy('id');
        $existingChannels = $this->fsPosterBridge->channelManagerRecordsForAgent($username)
            ->mapWithKeys(fn (array $channel) => [
                $channel['channel_session_id'] . ':' . $channel['remote_id'] . ':' . $channel['channel_type'] => true,
            ])
            ->all();

        $imported = 0;
        $updated = 0;
        $seen = [];

        foreach ((array) $validated['channels'] as $channel) {
            $sessionId = (int) ($channel['channel_session_id'] ?? 0);
            $channelType = trim((string) ($channel['channel_type'] ?? ''));
            $remoteId = trim((string) ($channel['remote_id'] ?? ''));

            if ($sessionId <= 0 || $channelType === '' || $remoteId === '') {
                continue;
            }

            $session = $sessions->get($sessionId);

            if (! is_array($session)) {
                throw ValidationException::withMessages([
                    'channels' => 'FS Poster connected the account under a different WordPress user. Sign in to WordPress with the matching agent account and try again.',
                ]);
            }

            if (($session['social_network'] ?? '') !== $socialNetwork) {
                throw ValidationException::withMessages([
                    'channels' => 'The returned channels do not match the selected network.',
                ]);
            }

            $channelKey = $sessionId . ':' . $remoteId . ':' . $channelType;

            if (isset($seen[$channelKey])) {
                continue;
            }

            $seen[$channelKey] = true;
            $alreadyExists = isset($existingChannels[$channelKey]);
            $channelDataJson = json_encode((array) ($channel['data'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if (! is_string($channelDataJson)) {
                $channelDataJson = '[]';
            }

            $this->fsPosterBridge->createChannel($username, [
                'channel_session_id' => $sessionId,
                'name' => trim((string) ($channel['name'] ?? '')),
                'channel_type' => $channelType,
                'remote_id' => $remoteId,
                'picture' => trim((string) ($channel['picture'] ?? '')),
                'status' => '1',
                'auto_share' => '1',
                'data_json' => $channelDataJson,
                'custom_settings_json' => '',
            ]);

            if ($alreadyExists) {
                $updated++;
            } else {
                $imported++;
                $existingChannels[$channelKey] = true;
            }
        }

        if ($imported === 0 && $updated === 0) {
            throw ValidationException::withMessages([
                'channels' => 'FS Poster finished the login, but no channels were returned to import.',
            ]);
        }

        $message = $imported > 0
            ? "{$imported} channel" . ($imported === 1 ? '' : 's') . " connected" . ($updated > 0 ? " and {$updated} updated" : '') . '.'
            : "{$updated} channel" . ($updated === 1 ? '' : 's') . ' refreshed from FS Poster.';

        $request->session()->flash('success', $message);

        return response()->json([
            'message' => $message,
            'redirect_url' => route('social.channels.index', [
                'network' => $socialNetwork,
            ]),
        ]);
    }

    public function updateChannel(Request $request, int $channelId)
    {
        $username = Auth::guard('agent')->user()->username;
        $existingChannel = $this->fsPosterBridge->findChannelForAgent($username, $channelId);
        $validated = $this->validateChannelWizard($request, true);
        $sessionId = (int) ($validated['channel_session_id'] ?? $existingChannel['channel_session_id']);

        $this->fsPosterBridge->updateChannel($username, $channelId, [
            'channel_session_id' => $sessionId,
            'name' => $validated['name'],
            'channel_type' => $validated['channel_type'],
            'remote_id' => $validated['remote_id'],
            'picture' => $validated['picture'] ?? '',
            'status' => $validated['status'] ?? '1',
            'auto_share' => $validated['auto_share'] ?? '1',
            'proxy' => ($validated['proxy_enabled'] ?? '0') === '1' ? ($validated['proxy'] ?? '') : '',
            'data_json' => $validated['data_json'] ?? $existingChannel['data_json'],
            'custom_settings_json' => $validated['custom_settings_json'] ?? $existingChannel['custom_settings_json'],
        ]);

        return redirect()
            ->route('social.channels.index', [
                'network' => $existingChannel['social_network'],
            ])
            ->with('success', 'The channel was updated.');
    }

    public function refreshChannel(int $channelId)
    {
        $username = Auth::guard('agent')->user()->username;
        $this->fsPosterBridge->findChannelForAgent($username, $channelId);

        try {
            $result = $this->fsPosterBridge->refreshChannelFromWordpress($username, $channelId);
        } catch (ValidationException $exception) {
            return redirect()->back()->withErrors($exception->errors());
        } catch (\Throwable $exception) {
            return redirect()->back()->withErrors([
                'channel' => trim((string) $exception->getMessage()) !== ''
                    ? trim((string) $exception->getMessage())
                    : 'FS Poster could not refresh the channel.',
            ]);
        }

        return redirect()->back()->with('success', trim((string) ($result['message'] ?? '')) !== ''
            ? trim((string) $result['message'])
            : 'The channel was refreshed.');
    }

    public function destroyChannel(int $channelId)
    {
        $username = Auth::guard('agent')->user()->username;
        $channel = $this->fsPosterBridge->findChannelForAgent($username, $channelId);
        $this->fsPosterBridge->deleteChannel($username, $channelId);

        return redirect()
            ->route('social.channels.index', [
                'network' => $channel['social_network'],
            ])
            ->with('success', 'The channel was removed.');
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

    private function validateChannelWizard(Request $request, bool $editing): array
    {
        $validated = $request->validate([
            'social_network' => [$editing ? 'nullable' : 'required_without:channel_session_id', 'string', 'max:50'],
            'channel_session_id' => ['nullable', 'integer'],
            'session_mode' => ['nullable', 'in:existing,new'],
            'account_name' => ['nullable', 'string', 'max:500'],
            'account_remote_id' => ['nullable', 'string', 'max:100'],
            'method' => ['nullable', 'string', 'max:100'],
            'proxy_enabled' => ['nullable', 'in:0,1'],
            'proxy' => ['nullable', 'string', 'max:500'],
            'name' => ['required', 'string', 'max:200'],
            'channel_type' => ['required', 'string', 'max:30'],
            'remote_id' => ['required', 'string', 'max:100'],
            'picture' => ['nullable', 'string', 'max:5000'],
            'data_json' => ['nullable', 'string'],
            'custom_settings_json' => ['nullable', 'string'],
            'status' => ['nullable', 'in:0,1'],
            'auto_share' => ['nullable', 'in:0,1'],
        ]);

        if ((int) ($validated['channel_session_id'] ?? 0) === 0) {
            $missing = [];

            if (trim((string) ($validated['social_network'] ?? '')) === '') {
                $missing['social_network'] = 'Choose a social network first.';
            }

            if (trim((string) ($validated['account_name'] ?? '')) === '') {
                $missing['account_name'] = 'Enter the account name before adding the channel.';
            }

            if (trim((string) ($validated['account_remote_id'] ?? '')) === '') {
                $missing['account_remote_id'] = 'Enter the account ID before adding the channel.';
            }

            if ($missing !== []) {
                throw ValidationException::withMessages($missing);
            }
        }

        $this->assertValidJson((string) ($validated['data_json'] ?? '[]'), 'data_json');

        if (array_key_exists('custom_settings_json', $validated) && trim((string) $validated['custom_settings_json']) !== '') {
            $this->assertValidJson((string) $validated['custom_settings_json'], 'custom_settings_json');
        }

        return $validated;
    }

    private function resolveChannelSessionId(string $username, array $validated): int
    {
        $sessionId = (int) ($validated['channel_session_id'] ?? 0);

        if ($sessionId > 0) {
            $this->fsPosterBridge->findChannelSessionForAgent($username, $sessionId);

            return $sessionId;
        }

        return $this->fsPosterBridge->createChannelSession($username, [
            'name' => trim((string) ($validated['account_name'] ?? '')),
            'social_network' => trim((string) ($validated['social_network'] ?? '')),
            'remote_id' => trim((string) ($validated['account_remote_id'] ?? '')),
            'method' => trim((string) ($validated['method'] ?? '')) !== '' ? trim((string) $validated['method']) : 'app',
            'proxy' => ($validated['proxy_enabled'] ?? '0') === '1' ? trim((string) ($validated['proxy'] ?? '')) : '',
            'data_json' => "{\n  \"auth_data\": {}\n}",
        ]);
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
            ['key' => 'tiktok', 'label' => 'TikTok'],
            ['key' => 'threads', 'label' => 'Threads'],
            ['key' => 'twitter', 'label' => 'X (Twitter)'],
            ['key' => 'linkedin', 'label' => 'LinkedIn'],
            ['key' => 'pinterest', 'label' => 'Pinterest'],
            ['key' => 'flickr', 'label' => 'Flickr'],
            ['key' => 'telegram', 'label' => 'Telegram'],
            ['key' => 'reddit', 'label' => 'Reddit'],
            ['key' => 'youtube', 'label' => 'YouTube'],
            ['key' => 'tumblr', 'label' => 'Tumblr'],
            ['key' => 'ok', 'label' => 'Odnoklassniki'],
            ['key' => 'google_b', 'label' => 'Google Business'],
            ['key' => 'blogger', 'label' => 'Blogger'],
            ['key' => 'medium', 'label' => 'Medium'],
            ['key' => 'vk', 'label' => 'VK'],
            ['key' => 'plurk', 'label' => 'Plurk'],
            ['key' => 'truthsocial', 'label' => 'Truth Social'],
        ];
    }

    /**
     * @return array<int, array{key:string,label:string}>
     */
    private function channelTypeOptions(string $network): array
    {
        return match ($network) {
            'fb' => [
                ['key' => 'account', 'label' => 'Profile'],
                ['key' => 'ownpage', 'label' => 'Page'],
                ['key' => 'group', 'label' => 'Group'],
                ['key' => 'account_story', 'label' => 'Story'],
                ['key' => 'ownpage_story', 'label' => 'Page story'],
            ],
            'instagram' => [
                ['key' => 'account', 'label' => 'Feed'],
                ['key' => 'account_story', 'label' => 'Story'],
            ],
            'linkedin' => [
                ['key' => 'account', 'label' => 'Profile'],
                ['key' => 'company', 'label' => 'Company page'],
            ],
            'pinterest' => [
                ['key' => 'board', 'label' => 'Board'],
            ],
            'google_b' => [
                ['key' => 'location', 'label' => 'Location'],
            ],
            'tumblr' => [
                ['key' => 'blog', 'label' => 'Blog'],
            ],
            'youtube' => [
                ['key' => 'channel', 'label' => 'Channel'],
            ],
            default => [
                ['key' => 'account', 'label' => 'Account'],
            ],
        };
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

    /**
     * @return array<int, string>
     */
    private function popupAuthNetworks(): array
    {
        return [
            'fb',
            'instagram',
            'linkedin',
            'pinterest',
            'reddit',
            'twitter',
            'tiktok',
            'threads',
            'google_b',
            'tumblr',
            'medium',
            'blogger',
            'ok',
            'plurk',
            'flickr',
        ];
    }

    private function supportsPopupAuth(string $network): bool
    {
        return in_array($network, $this->popupAuthNetworks(), true);
    }
}
