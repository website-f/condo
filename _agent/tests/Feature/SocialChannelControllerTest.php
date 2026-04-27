<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Support\CondoPackageManager;
use App\Support\FsPosterBridge;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class SocialChannelControllerTest extends TestCase
{
    public function test_oauth_import_rejects_networks_without_popup_auth_support(): void
    {
        $agent = new Agent(['id' => 1, 'username' => 'agent1']);
        $this->actingAs($agent, 'agent');

        $packageManager = Mockery::mock(CondoPackageManager::class);
        $packageManager->shouldReceive('hasAccess')->andReturnTrue();
        $this->app->instance(CondoPackageManager::class, $packageManager);

        $this->app->instance(FsPosterBridge::class, Mockery::mock(FsPosterBridge::class));

        $response = $this->post('/social/channels/oauth-import', [
            'social_network' => 'telegram',
            'channels' => [
                [
                    'channel_session_id' => 10,
                    'channel_type' => 'account',
                    'remote_id' => 'remote-1',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('social_network');
    }

    public function test_oauth_import_deduplicates_channels_and_returns_json_success(): void
    {
        $agent = new Agent(['id' => 1, 'username' => 'agent1']);
        $this->actingAs($agent, 'agent');

        $packageManager = Mockery::mock(CondoPackageManager::class);
        $packageManager->shouldReceive('hasAccess')->andReturnTrue();
        $this->app->instance(CondoPackageManager::class, $packageManager);

        $bridge = Mockery::mock(FsPosterBridge::class);
        $bridge->shouldReceive('channelSessionsForAgent')
            ->once()
            ->with('agent1')
            ->andReturn(new Collection([
                [
                    'id' => 10,
                    'social_network' => 'fb',
                ],
            ]));
        $bridge->shouldReceive('channelManagerRecordsForAgent')
            ->once()
            ->with('agent1')
            ->andReturn(new Collection([
                [
                    'channel_session_id' => 10,
                    'remote_id' => 'page-1',
                    'channel_type' => 'ownpage',
                ],
            ]));
        $bridge->shouldReceive('createChannel')
            ->once()
            ->with(
                'agent1',
                Mockery::on(fn (array $payload) => (int) $payload['channel_session_id'] === 10
                    && $payload['remote_id'] === 'page-1'
                    && $payload['channel_type'] === 'ownpage')
            );
        $this->app->instance(FsPosterBridge::class, $bridge);

        $response = $this->postJson('/social/channels/oauth-import', [
            'social_network' => 'fb',
            'channels' => [
                [
                    'channel_session_id' => 10,
                    'channel_type' => 'ownpage',
                    'remote_id' => 'page-1',
                    'name' => 'Main Page',
                    'data' => ['hello' => 'world'],
                ],
                [
                    'channel_session_id' => 10,
                    'channel_type' => 'ownpage',
                    'remote_id' => 'page-1',
                    'name' => 'Duplicate',
                    'data' => ['hello' => 'again'],
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson([
            'message' => '1 channel refreshed from FS Poster.',
            'redirect_url' => route('social.channels.index', ['network' => 'fb']),
        ]);
    }
}
