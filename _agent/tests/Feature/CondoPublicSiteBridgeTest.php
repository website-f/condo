<?php

namespace Tests\Feature;

use App\Models\CondoAgentDomain;
use App\Models\CondoListing;
use App\Models\CondoProperty;
use App\Support\CondoPublicSiteSynchronizer;
use App\Support\RankMathBridge;
use Mockery;
use Tests\Concerns\UsesBridgeTestDatabases;
use Tests\TestCase;

class CondoPublicSiteBridgeTest extends TestCase
{
    use UsesBridgeTestDatabases;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootBridgeTestDatabases();
        $this->seedAgent('agent1', 1);
        $this->seedAgent('agent2', 2);
        $this->seedCondoWordpressUser('agent1', 1);
        $this->seedCondoWordpressUser('agent2', 2);

        $rankMathBridge = Mockery::mock(RankMathBridge::class);
        $rankMathBridge->shouldReceive('currentSeoData')->andReturn([
            'meta_title' => 'Bridge SEO title',
            'meta_description' => 'Bridge SEO description',
            'focus_keyword' => 'bridge keyword',
            'canonical_url' => null,
            'og_title' => null,
            'og_description' => null,
            'twitter_title' => null,
            'twitter_description' => null,
            'robots' => ['index', 'follow'],
        ])->byDefault();
        $this->app->instance(RankMathBridge::class, $rankMathBridge);
    }

    public function test_sync_listing_creates_public_projection_and_default_domain(): void
    {
        $listingId = $this->createCondoListingRecord([
            'post_title' => 'Skyline Residence',
            'post_name' => 'skyline-residence',
        ], [
            'condo_agent_username' => 'agent1',
            'condo_propertyid' => 'PROP-100',
            'condo_propertytype' => 'Condominium',
            'condo_listingtype' => 'Sale',
            'condo_price' => '750000',
            'condo_state' => 'Kuala Lumpur',
            'condo_area' => 'Ampang',
            'Descriptions' => 'A bright condo listing for bridge testing.',
        ]);

        /** @var CondoListing $listing */
        $listing = CondoListing::query()->with('details')->findOrFail($listingId);
        $result = $this->app->make(CondoPublicSiteSynchronizer::class)->syncListing($listing, 'agent1');

        $this->assertSame('agent1.condo.com.my', $result['host']);
        $this->assertDatabaseHas('condo_agent_domains', [
            'agent_username' => 'agent1',
            'host' => 'agent1.condo.com.my',
            'is_active' => 1,
        ]);
        $this->assertDatabaseHas('condo_properties', [
            'agent_username' => 'agent1',
            'slug' => 'skyline-residence',
            'legacy_source' => 'condo_wordpress',
            'legacy_source_id' => (string) $listingId,
            'status' => 'active',
            'visibility' => 'public',
        ]);

        /** @var CondoProperty $property */
        $property = CondoProperty::withTrashed()
            ->with(['details', 'projection', 'seo'])
            ->where('legacy_source', 'condo_wordpress')
            ->where('legacy_source_id', (string) $listingId)
            ->firstOrFail();

        $this->assertSame('agent1', $property->agent_username);
        $this->assertSame($listingId, $property->projection?->wordpress_post_id);
        $this->assertSame('projected', $property->projection?->projection_status);
        $this->assertSame('Bridge SEO title', $property->seo?->meta_title);
        $this->assertDatabaseHas('condo_property_details', [
            'property_id' => $property->id,
        ]);
    }

    public function test_properties_api_returns_only_the_requested_agents_projected_listings(): void
    {
        $agentOneListingId = $this->createCondoListingRecord([
            'post_title' => 'Agent One Listing',
            'post_name' => 'agent-one-listing',
        ], [
            'condo_agent_username' => 'agent1',
            'condo_propertyid' => 'PROP-201',
            'condo_propertytype' => 'Condominium',
            'condo_listingtype' => 'Sale',
            'condo_state' => 'Selangor',
            'condo_area' => 'Subang',
        ]);
        $agentTwoListingId = $this->createCondoListingRecord([
            'post_title' => 'Agent Two Listing',
            'post_name' => 'agent-two-listing',
        ], [
            'condo_agent_username' => 'agent2',
            'condo_propertyid' => 'PROP-202',
            'condo_propertytype' => 'Apartment',
            'condo_listingtype' => 'Rent',
            'condo_state' => 'Johor',
            'condo_area' => 'Johor Bahru',
        ]);

        $synchronizer = $this->app->make(CondoPublicSiteSynchronizer::class);
        $synchronizer->syncListing(CondoListing::query()->with('details')->findOrFail($agentOneListingId), 'agent1');
        $synchronizer->syncListing(CondoListing::query()->with('details')->findOrFail($agentTwoListingId), 'agent2');

        $response = $this->getJson('/api/condo/agents/agent1.condo.com.my/properties');

        $response->assertOk()
            ->assertJsonPath('meta.host', 'agent1.condo.com.my')
            ->assertJsonPath('meta.agent_username', 'agent1')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Agent One Listing')
            ->assertJsonPath('data.0.agent.username', 'agent1');
    }

    public function test_reserved_subdomain_does_not_resolve_via_username_fallback(): void
    {
        $this->seedAgent('api', 3);

        $response = $this->getJson('/api/condo/agents/api.condo.com.my/properties');

        $response->assertNotFound();
    }

    public function test_explicit_custom_domain_mapping_still_resolves_agent_hosts(): void
    {
        CondoAgentDomain::query()->create([
            'agent_username' => 'agent1',
            'host' => 'homes.agent-one.test',
            'is_primary' => true,
            'is_active' => true,
            'ssl_status' => 'active',
        ]);

        $response = $this->getJson('/api/condo/agents/homes.agent-one.test');

        $response->assertOk()
            ->assertJsonPath('data.host', 'homes.agent-one.test')
            ->assertJsonPath('data.agent.username', 'agent1')
            ->assertJsonPath('data.domain.host', 'homes.agent-one.test');
    }
}
