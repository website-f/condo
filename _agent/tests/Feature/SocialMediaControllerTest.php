<?php

namespace Tests\Feature;

use App\Models\CondoListing;
use App\Support\CondoPackageManager;
use App\Support\FsPosterBridge;
use App\Support\RecentlyDeletedService;
use Illuminate\Support\Collection;
use Mockery;
use Tests\Concerns\UsesBridgeTestDatabases;
use Tests\TestCase;

class SocialMediaControllerTest extends TestCase
{
    use UsesBridgeTestDatabases;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootBridgeTestDatabases();
        $this->signInAgent();
        $this->seedCondoWordpressUser('agent1');
    }

    public function test_store_creates_schedule_group_for_owned_condo_listing(): void
    {
        $listingId = $this->createCondoListingRecord([
            'post_title' => 'Schedule Listing',
            'post_name' => 'schedule-listing',
        ], [
            'condo_agent_username' => 'agent1',
            'condo_propertyid' => 'PROP-300',
        ]);

        $packageManager = Mockery::mock(CondoPackageManager::class);
        $packageManager->shouldReceive('hasAccess')->andReturnTrue();
        $packageManager->shouldReceive('consumeCredit')->once();
        $this->app->instance(CondoPackageManager::class, $packageManager);

        $bridge = Mockery::mock(FsPosterBridge::class);
        $bridge->shouldReceive('availableChannels')
            ->once()
            ->with('agent1')
            ->andReturn(new Collection([
                [
                    'id' => 1,
                    'name' => 'Main Facebook Page',
                    'channel_type' => 'ownpage',
                    'social_network' => 'fb',
                ],
            ]));
        $bridge->shouldReceive('storeScheduleGroup')
            ->once()
            ->with(
                Mockery::type(CondoListing::class),
                'agent1',
                Mockery::on(fn (array $payload) => (int) $payload['listing_id'] === $listingId
                    && $payload['channel_ids'] === [1]
                    && isset($payload['channel_customizations'][1]))
            )
            ->andReturn('group-123');
        $bridge->shouldReceive('scheduleCustomizationDefaultsForChannel')
            ->atLeast()->once()
            ->andReturn([
                'post_content' => '{post_title}',
            ]);
        $this->app->instance(FsPosterBridge::class, $bridge);

        $this->app->instance(RecentlyDeletedService::class, Mockery::mock(RecentlyDeletedService::class));

        $response = $this->post('/social', [
            'listing_id' => $listingId,
            'channel_ids' => [1],
            'scheduled_at' => now()->addHour()->format('Y-m-d H:i:s'),
            'message' => 'Fresh social post',
            'upload_media' => '0',
            'first_comment_enabled' => '0',
            'first_comment' => '',
        ]);

        $response->assertRedirect(route('social.edit', 'group-123'));
        $response->assertSessionHas('success', 'FS Poster schedule queued for this condo listing.');
    }
}
