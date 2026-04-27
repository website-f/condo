<?php

namespace Tests\Feature;

use App\Models\CondoListing;
use App\Support\CondoBridgeSynchronizer;
use App\Support\CondoPackageManager;
use App\Support\CondoWordpressBridge;
use App\Support\RecentlyDeletedService;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\Concerns\UsesBridgeTestDatabases;
use Tests\TestCase;

class ListingControllerTest extends TestCase
{
    use UsesBridgeTestDatabases;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootBridgeTestDatabases();
        $this->seedCondoWordpressUser('agent1');
    }

    public function test_condo_store_syncs_bridge_and_flashes_warnings(): void
    {
        $this->signInAgent();

        $packageManager = Mockery::mock(CondoPackageManager::class);
        $packageManager->shouldReceive('hasAccess')->andReturnTrue();
        $packageManager->shouldReceive('ensureCondoListingCapacity')->once();
        $this->app->instance(CondoPackageManager::class, $packageManager);

        $this->app->instance(RecentlyDeletedService::class, Mockery::mock(RecentlyDeletedService::class));

        $wordpressBridge = Mockery::mock(CondoWordpressBridge::class);
        $wordpressBridge->shouldReceive('propertyIdExists')->andReturnFalse();
        $wordpressBridge->shouldReceive('storeUploadedImages')->andReturn([]);
        $wordpressBridge->shouldReceive('deleteStoredImages')->andReturnNull()->byDefault();
        $wordpressBridge->shouldReceive('syncListing')->once();
        $this->app->instance(CondoWordpressBridge::class, $wordpressBridge);

        $synchronizer = Mockery::mock(CondoBridgeSynchronizer::class);
        $synchronizer->shouldReceive('syncListingMutation')
            ->once()
            ->with(
                'agent1',
                Mockery::type(CondoListing::class),
                'create',
                Mockery::on(fn (array $changes) => ($changes['post_status_changed'] ?? false) === true
                    && ($changes['post_date_changed'] ?? false) === true),
                true
            )
            ->andReturn(['WordPress cache: signed purge is not configured yet.']);
        $this->app->instance(CondoBridgeSynchronizer::class, $synchronizer);

        $response = $this->post('/listings', [
            'source' => 'condo',
            'propertyname' => 'Skyline Residence',
            'propertytype' => 'Condominium',
            'listingtype' => 'Sale',
            'price' => '750000',
            'state' => 'Kuala Lumpur',
            'area' => 'Ampang',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Listing created successfully.');
        $response->assertSessionHas('bridge_warnings', ['WordPress cache: signed purge is not configured yet.']);
        $this->assertSame(1, DB::connection('condo')->table('posts')->count());
    }

    public function test_condo_update_marks_term_changes_for_bridge_sync(): void
    {
        $this->signInAgent();

        $listingId = $this->createCondoListingRecord([
            'post_title' => 'Original Listing',
            'post_name' => 'original-listing',
        ], [
            'condo_agent_username' => 'agent1',
            'condo_propertyid' => 'PROP-100',
            'condo_propertytype' => 'Apartment',
            'condo_listingtype' => 'Sale',
            'condo_price' => '550000',
            'condo_state' => 'Kuala Lumpur',
            'condo_area' => 'Cheras',
        ]);

        $packageManager = Mockery::mock(CondoPackageManager::class);
        $packageManager->shouldReceive('hasAccess')->andReturnTrue();
        $this->app->instance(CondoPackageManager::class, $packageManager);

        $this->app->instance(RecentlyDeletedService::class, Mockery::mock(RecentlyDeletedService::class));

        $wordpressBridge = Mockery::mock(CondoWordpressBridge::class);
        $wordpressBridge->shouldReceive('storeUploadedImages')->andReturn([]);
        $wordpressBridge->shouldReceive('deleteStoredImages')->andReturnNull()->byDefault();
        $wordpressBridge->shouldReceive('syncListing')->once();
        $this->app->instance(CondoWordpressBridge::class, $wordpressBridge);

        $synchronizer = Mockery::mock(CondoBridgeSynchronizer::class);
        $synchronizer->shouldReceive('syncListingMutation')
            ->once()
            ->with(
                'agent1',
                Mockery::type(CondoListing::class),
                'update',
                Mockery::on(fn (array $changes) => ($changes['post_terms_changed'] ?? false) === true
                    && ($changes['post_status_changed'] ?? false) === false
                    && ($changes['post_date_changed'] ?? false) === false),
                true
            )
            ->andReturn([]);
        $this->app->instance(CondoBridgeSynchronizer::class, $synchronizer);

        $response = $this->put('/listings/' . $listingId, [
            'source' => 'condo',
            'original_source' => 'condo',
            'return_source' => 'condo',
            'propertyname' => 'Original Listing',
            'propertytype' => 'Condominium',
            'listingtype' => 'Rent',
            'price' => '4500',
            'state' => 'Kuala Lumpur',
            'area' => 'Cheras',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Listing updated successfully.');

        $this->assertSame(
            'Condominium',
            DB::connection('condo')->table('postmeta')->where('post_id', $listingId)->where('meta_key', 'condo_propertytype')->value('meta_value')
        );
        $this->assertSame(
            'Rent',
            DB::connection('condo')->table('postmeta')->where('post_id', $listingId)->where('meta_key', 'condo_listingtype')->value('meta_value')
        );
    }

    public function test_condo_destroy_trashes_listing_and_syncs_bridge(): void
    {
        $this->signInAgent();

        $listingId = $this->createCondoListingRecord([
            'post_title' => 'Delete Me',
            'post_name' => 'delete-me',
        ], [
            'condo_agent_username' => 'agent1',
            'condo_propertyid' => 'PROP-200',
        ]);

        $packageManager = Mockery::mock(CondoPackageManager::class);
        $packageManager->shouldReceive('hasAccess')->andReturnTrue();
        $this->app->instance(CondoPackageManager::class, $packageManager);

        $recentlyDeleted = Mockery::mock(RecentlyDeletedService::class);
        $recentlyDeleted->shouldReceive('rememberListing')->once();
        $this->app->instance(RecentlyDeletedService::class, $recentlyDeleted);

        $synchronizer = Mockery::mock(CondoBridgeSynchronizer::class);
        $synchronizer->shouldReceive('syncListingMutation')
            ->once()
            ->with(
                'agent1',
                Mockery::type(CondoListing::class),
                'trash',
                Mockery::on(fn (array $changes) => ($changes['post_status_changed'] ?? false) === true
                    && ($changes['previous_status'] ?? null) === 'publish'),
                false
            )
            ->andReturn([]);
        $this->app->instance(CondoBridgeSynchronizer::class, $synchronizer);

        $response = $this->delete('/listings/' . $listingId, [
            'source' => 'condo',
            'return_source' => 'condo',
        ]);

        $response->assertRedirect(route('listings.index', ['source' => 'condo']));
        $response->assertSessionHas('success', 'Listing moved to Recently Deleted.');
        $this->assertSame(
            'trash',
            DB::connection('condo')->table('posts')->where('ID', $listingId)->value('post_status')
        );
    }
}
