<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Support\RecentlyDeletedService;
use Mockery;
use Tests\TestCase;

class RecentlyDeletedControllerTest extends TestCase
{
    public function test_restore_flashes_bridge_warnings_from_service(): void
    {
        $agent = new Agent(['id' => 1, 'username' => 'agent1']);
        $this->actingAs($agent, 'agent');

        $service = Mockery::mock(RecentlyDeletedService::class);
        $service->shouldReceive('restore')
            ->once()
            ->with('agent1', 'listing_condo', '55')
            ->andReturn([
                'message' => 'Condo listing restored.',
                'bridge_warnings' => ['FS Poster: no schedules were created.'],
            ]);
        $this->app->instance(RecentlyDeletedService::class, $service);

        $response = $this->post('/recently-deleted/restore', [
            'type' => 'listing_condo',
            'key' => '55',
        ]);

        $response->assertRedirect(route('recently-deleted.index'));
        $response->assertSessionHas('success', 'Condo listing restored.');
        $response->assertSessionHas('bridge_warnings', ['FS Poster: no schedules were created.']);
    }
}
