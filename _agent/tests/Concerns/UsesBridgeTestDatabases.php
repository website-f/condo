<?php

namespace Tests\Concerns;

use App\Models\Agent;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait UsesBridgeTestDatabases
{
    protected function bootBridgeTestDatabases(): void
    {
        config([
            'session.driver' => 'array',
            'database.default' => 'mysql',
        ]);

        $this->configureSqliteConnection('mysql');
        $this->configureSqliteConnection('mysql2');
        $this->configureSqliteConnection('condo');

        $this->createMysqlTables();
        $this->createMysql2Tables();
        $this->createCondoTables();
    }

    protected function signInAgent(string $username = 'agent1', int $id = 1): Agent
    {
        $this->seedAgent($username, $id);

        /** @var Agent $agent */
        $agent = Agent::query()->findOrFail($id);
        $this->actingAs($agent, 'agent');

        return $agent;
    }

    protected function seedAgent(string $username, int $id = 1): void
    {
        DB::connection('mysql')->table('Users')->updateOrInsert(
            ['id' => $id],
            [
                'username' => $username,
                'package' => 1,
                'credit' => 10,
                'creditenddate' => now()->toDateString(),
            ]
        );

        DB::connection('mysql')->table('UserDetails')->updateOrInsert(
            ['username' => $username],
            [
                'email' => $username . '@example.test',
                'firstname' => 'Bridge',
                'lastname' => 'Tester',
                'phone' => '0123456789',
            ]
        );
    }

    protected function seedCondoWordpressUser(string $username, int $id = 1): void
    {
        DB::connection('condo')->table('users')->updateOrInsert(
            ['ID' => $id],
            [
                'user_login' => $username,
                'user_email' => $username . '@example.test',
            ]
        );
    }

    protected function createCondoListingRecord(array $attributes = [], array $meta = []): int
    {
        $now = Carbon::now();
        $postId = DB::connection('condo')->table('posts')->insertGetId(array_merge([
            'post_author' => 1,
            'post_date' => $now->format('Y-m-d H:i:s'),
            'post_date_gmt' => $now->clone()->utc()->format('Y-m-d H:i:s'),
            'post_content' => '',
            'post_title' => 'Bridge Test Listing',
            'post_excerpt' => '',
            'post_status' => 'publish',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_password' => '',
            'post_name' => 'bridge-test-listing',
            'to_ping' => '',
            'pinged' => '',
            'post_modified' => $now->format('Y-m-d H:i:s'),
            'post_modified_gmt' => $now->clone()->utc()->format('Y-m-d H:i:s'),
            'post_content_filtered' => '',
            'post_parent' => 0,
            'guid' => 'https://condo.test/?post_type=properties&p=1',
            'menu_order' => 0,
            'post_type' => 'properties',
            'post_mime_type' => '',
            'comment_count' => 0,
        ], $attributes));

        $defaults = [
            'condo_agent_username' => 'agent1',
            'condo_propertyid' => 'PROP-' . $postId,
            'condo_propertytype' => 'Condominium',
            'condo_listingtype' => 'Sale',
            'condo_price' => '500000',
            'condo_state' => 'Kuala Lumpur',
            'condo_area' => 'Ampang',
            'condo_keywords' => 'Bridge test listing',
            'condo_totalphoto' => '0',
            'condo_cobroke' => '0',
            'Descriptions' => '',
            'Photos' => '',
        ];

        foreach (array_merge($defaults, $meta) as $key => $value) {
            DB::connection('condo')->table('postmeta')->insert([
                'post_id' => $postId,
                'meta_key' => $key,
                'meta_value' => $value,
            ]);
        }

        return $postId;
    }

    private function configureSqliteConnection(string $connection): void
    {
        config([
            "database.connections.{$connection}" => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);

        DB::purge($connection);
    }

    private function createMysqlTables(): void
    {
        $schema = Schema::connection('mysql');

        $schema->create('Users', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('username')->unique();
            $table->integer('package')->nullable();
            $table->integer('credit')->nullable();
            $table->string('creditenddate')->nullable();
        });

        $schema->create('UserDetails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->unique();
            $table->string('email')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('phone')->nullable();
        });

        $schema->create('Packages', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name')->nullable();
            $table->integer('creditlimit')->nullable();
            $table->integer('maxaccount')->nullable();
            $table->integer('token')->nullable();
            $table->integer('icp_limit')->nullable();
            $table->integer('ipp_limit')->nullable();
            $table->integer('local_limit')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('color')->nullable();
            $table->integer('is_unknown')->default(0);
            $table->integer('group_limit')->nullable();
            $table->integer('join_group_limit')->nullable();
        });

        DB::connection('mysql')->table('Packages')->insert([
            'id' => 1,
            'name' => 'Condo Premium Package',
            'creditlimit' => 100,
            'maxaccount' => 500,
            'token' => 0,
            'icp_limit' => 0,
            'ipp_limit' => 0,
            'local_limit' => 0,
            'cost' => 0,
            'color' => null,
            'is_unknown' => 0,
            'group_limit' => 0,
            'join_group_limit' => 0,
        ]);

        $schema->create('cms_bridge_sync_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('agent_username')->nullable();
            $table->string('resource_type');
            $table->string('resource_key');
            $table->string('sync_target');
            $table->string('last_operation')->nullable();
            $table->string('sync_status');
            $table->text('last_message')->nullable();
            $table->text('last_error')->nullable();
            $table->text('last_context')->nullable();
            $table->dateTime('last_synced_at')->nullable();
            $table->timestamps();
        });

        $schema->create('condo_agent_domains', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('agent_username');
            $table->string('host')->unique();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('ssl_status')->nullable();
            $table->timestamps();
        });

        $schema->create('condo_properties', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->string('agent_username');
            $table->string('property_uid')->nullable();
            $table->string('slug');
            $table->string('title')->nullable();
            $table->string('property_type')->nullable();
            $table->string('listing_type')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('area')->nullable();
            $table->string('township')->nullable();
            $table->string('postcode')->nullable();
            $table->string('address')->nullable();
            $table->text('description')->nullable();
            $table->text('keywords')->nullable();
            $table->integer('cobroke')->default(0);
            $table->string('status')->default('inactive');
            $table->string('visibility')->default('private');
            $table->dateTime('published_at')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('legacy_source')->nullable();
            $table->string('legacy_source_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $schema->create('condo_property_details', function (Blueprint $table) {
            $table->unsignedBigInteger('property_id')->primary();
            $table->string('bedrooms')->nullable();
            $table->string('bathrooms')->nullable();
            $table->string('built_up_area')->nullable();
            $table->string('land_area')->nullable();
            $table->string('floor')->nullable();
            $table->string('car_park')->nullable();
            $table->string('furnishing')->nullable();
            $table->string('year_built')->nullable();
            $table->string('tenure')->nullable();
            $table->string('title_type')->nullable();
            $table->string('land_title_type')->nullable();
            $table->string('occupancy')->nullable();
            $table->string('unit_type')->nullable();
            $table->string('bumiputera_lot')->nullable();
            $table->string('negotiable')->nullable();
            $table->string('rent_deposit')->nullable();
            $table->string('auction_date')->nullable();
            $table->string('auction_number')->nullable();
            $table->string('new_launch_units')->nullable();
            $table->text('features')->nullable();
            $table->text('raw_general_legacy')->nullable();
            $table->timestamps();
        });

        $schema->create('condo_property_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('property_id');
            $table->string('storage_disk')->nullable();
            $table->text('path')->nullable();
            $table->string('path_hash')->nullable();
            $table->text('public_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->string('checksum')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('mime_type')->nullable();
            $table->timestamps();
        });

        $schema->create('condo_property_seo', function (Blueprint $table) {
            $table->unsignedBigInteger('property_id')->primary();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('focus_keyword')->nullable();
            $table->text('canonical_url')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->text('robots_json')->nullable();
            $table->timestamps();
        });

        $schema->create('condo_wp_projections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('property_id')->unique();
            $table->integer('wordpress_post_id')->nullable()->unique();
            $table->integer('wordpress_blog_id')->default(1);
            $table->string('projection_status')->nullable();
            $table->string('projection_hash')->nullable();
            $table->dateTime('last_projected_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    private function createMysql2Tables(): void
    {
        $schema = Schema::connection('mysql2');

        $schema->create('Posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('propertyid')->nullable();
            $table->string('username')->nullable();
            $table->boolean('isDeleted')->default(false);
        });

        $schema->create('PostDetails', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('postid')->nullable();
            $table->string('meta_key')->nullable();
            $table->text('meta_value')->nullable();
        });
    }

    private function createCondoTables(): void
    {
        $schema = Schema::connection('condo');

        $schema->create('posts', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('post_author')->default(1);
            $table->dateTime('post_date')->nullable();
            $table->dateTime('post_date_gmt')->nullable();
            $table->text('post_content')->nullable();
            $table->string('post_title')->nullable();
            $table->text('post_excerpt')->nullable();
            $table->string('post_status')->nullable();
            $table->string('comment_status')->nullable();
            $table->string('ping_status')->nullable();
            $table->string('post_password')->nullable();
            $table->string('post_name')->nullable();
            $table->text('to_ping')->nullable();
            $table->text('pinged')->nullable();
            $table->dateTime('post_modified')->nullable();
            $table->dateTime('post_modified_gmt')->nullable();
            $table->text('post_content_filtered')->nullable();
            $table->integer('post_parent')->default(0);
            $table->string('guid')->nullable();
            $table->integer('menu_order')->default(0);
            $table->string('post_type')->nullable();
            $table->string('post_mime_type')->nullable();
            $table->integer('comment_count')->default(0);
        });

        $schema->create('postmeta', function (Blueprint $table) {
            $table->increments('meta_id');
            $table->integer('post_id');
            $table->string('meta_key')->nullable();
            $table->text('meta_value')->nullable();
        });

        $schema->create('terms', function (Blueprint $table) {
            $table->increments('term_id');
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->integer('term_group')->default(0);
        });

        $schema->create('term_taxonomy', function (Blueprint $table) {
            $table->increments('term_taxonomy_id');
            $table->integer('term_id');
            $table->string('taxonomy')->nullable();
            $table->text('description')->nullable();
            $table->integer('parent')->default(0);
            $table->integer('count')->default(0);
        });

        $schema->create('term_relationships', function (Blueprint $table) {
            $table->integer('object_id');
            $table->integer('term_taxonomy_id');
            $table->integer('term_order')->default(0);
        });

        $schema->create('users', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('user_login')->nullable();
            $table->string('user_email')->nullable();
        });

        $schema->create('fsp_channel_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('created_by')->default(0);
            $table->string('social_network')->nullable();
            $table->string('name')->nullable();
        });

        $schema->create('fsp_channels', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('channel_session_id')->default(0);
            $table->string('name')->nullable();
            $table->string('channel_type')->nullable();
            $table->string('picture')->nullable();
            $table->integer('auto_share')->default(1);
            $table->integer('status')->default(1);
            $table->integer('is_deleted')->default(0);
            $table->string('remote_id')->nullable();
            $table->text('data')->nullable();
            $table->text('custom_settings')->nullable();
        });

        $schema->create('fsp_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('wp_post_id')->default(0);
            $table->integer('channel_id')->default(0);
            $table->integer('user_id')->default(0);
            $table->string('status')->nullable();
            $table->string('group_id')->nullable();
            $table->dateTime('send_time')->nullable();
            $table->text('data')->nullable();
            $table->text('customization_data')->nullable();
            $table->timestamps();
        });
    }
}
