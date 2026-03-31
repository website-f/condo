<?php

namespace FSPoster\App\Providers\Core;

use FSPoster\App\AI\App\Bootstrap as AIBootstrap;
use FSPoster\App\Models\Planner;
use FSPoster\App\Pages\ChannelLabel\Repositories\ChannelLabelRepository;
use FSPoster\App\Pages\ChannelLabel\Services\ChannelLabelService;
use FSPoster\App\Pages\Notification\NotificationModule;
use FSPoster\App\Pages\Settings\Workflow\WorkflowModule;
use FSPoster\App\Providers\Context\UserContext;
use FSPoster\App\Providers\DB\DB;
use FSPoster\App\Providers\Factories\UserFactory;
use FSPoster\App\Providers\Helpers\Date;
use FSPoster\App\Providers\Helpers\Helper;
use FSPoster\App\Providers\Helpers\PluginHelper;
use FSPoster\App\Providers\License\LicenseAdapter;
use FSPoster\App\Providers\License\LicenseApiClient;
use FSPoster\App\Providers\License\LicenseApiClientFactory;
use FSPoster\App\Providers\Schedules\ScheduleModule;
use FSPoster\App\Providers\SocialNetwork\CallbackUrlHandler;
use FSPoster\App\Providers\WPPost\WPPostService;
use ReflectionException;

class Bootstrap
{
    /**
     * Bootstrap constructor.
     * @throws ReflectionException
     */
    public function __construct ()
    {
        $this->registerDefines();
        $this->registerContainers();
        $this->registerActivationHook();
	    $this->loadPluginTextDomain();
	    $this->loadPluginLinks();
	    $this->registerCustomPostTypesAndMetas();
	    $this->createPostSaveEvent();
	    $this->registerUserDeletedEvent();
		$this->registerCallbackUrlHandler();

        if ( PluginHelper::isPluginActivated() )
        {
	        LicenseAdapter::fetchAndRunMigrationData();
            CronJob::init();
        }

        add_action('plugins_loaded', function () {
            $this->registerUserContext();
            WorkflowModule::init();
            ScheduleModule::init();
            NotificationModule::init();
            AIBootstrap::init();

            Route::init();
        });


        add_action( 'init', function ()
        {
            if ( is_admin() ) {
                new BackEnd();
            }
            else {
                new FrontEnd();
            }
        } );

	    /**
	     * Ayri init-e salinmasinda sebeb, priority yuxari qoymagdiki, en sonda check edib header`i set etsin.
	     */
	    add_action( 'init', function ()
	    {
		    if ( is_admin() && Request::get('page', '', 'string') === FSP_PLUGIN_MENU_SLUG ) {
			    Helper::setCrossOriginOpenerPolicyHeaderIfNeed();
		    }
	    }, 9999999 );
    }

    private function registerDefines (): void
    {
		define( 'FSP_PLUGIN_SLUG', 'fs-poster' );
		define( 'FSP_PLUGIN_MENU_SLUG', 'fs-poster' );
        define( 'FSP_ROOT_DIR', dirname( __DIR__, 3 ) );
        define( 'FSP_ROOT_DIR_URL', dirname( plugin_dir_url( __DIR__ ), 2 ) );
        define( 'FSP_OAUTH_API_URL', 'https://www.fs-poster.com/oauth/' );
    }

    private function loadPluginLinks (): void
    {
        add_filter( 'plugin_action_links_fs-poster/init.php', function ( $links )
        {
            $newLinks = [
                '<a href="https://support.fs-code.com" target="_blank">' . fsp__( 'Support' ) . '</a>',
                '<a href="https://www.fs-poster.com/documentation/" target="_blank">' . fsp__( 'Documentation' ) . '</a>',
            ];

            return array_merge( $newLinks, $links );
        } );
    }

    private function loadPluginTextDomain (): void
    {
        add_action( 'plugins_loaded', [LocalizationService::class, 'loadTextDomain']);
    }

    private function registerCustomPostTypesAndMetas (): void
    {
        add_action( 'init', function ()
        {
            register_post_type( 'fsp_post', [
                'labels'      => [
                    'name'          => fsp__( 'FS Posts' ),
                    'singular_name' => fsp__( 'FS Post' ),
                ],
                'public'      => false,
                'has_archive' => true,
            ] );
        } );
    }

    private function createPostSaveEvent (): void
    {
        add_action( 'delete_post', [WPPostService::class, 'deletePostSchedules' ] );

        add_action( 'save_post', [WPPostService::class, 'postSaved' ], 10, 3 );
        add_action( 'pre_post_update', [WPPostService::class, 'postPreUpdated' ], 10, 2 );
        add_action( 'post_updated', [WPPostService::class, 'postUpdated' ], 10, 3 );

		add_action( 'set_object_terms', [WPPostService::class, 'setObjectTerms' ], 10, 6 );
    }

    private function registerActivationHook (): void
    {
        register_activation_hook( FSP_ROOT_DIR . '/init.php', function ()
        {
            if ( Settings::get( 'installed_version', '0', true ) )
            {
                $nowDateTime = Date::dateTimeSQL();
                Planner::where( 'status', 'active' )->where( 'share_type', 'interval' )->where( 'next_execute_at', '<=', $nowDateTime )->update( [
                    'next_execute_at' => DB::field( DB::raw( 'DATE_ADD(`next_execute_at`, INTERVAL ((TIMESTAMPDIFF(MINUTE, `next_execute_at`, %s) DIV (schedule_interval DIV 60) ) + 1) * (schedule_interval DIV 60) minute)', [ $nowDateTime ] ) ),
                ] );
            }
        } );
    }

	private function registerUserDeletedEvent (): void
    {
		add_action( 'deleted_user', [ Helper::class, 'clearUserAllData' ] );
	}

	private function registerCallbackUrlHandler (): void
    {
		add_action( 'init', [ CallbackUrlHandler::class, 'handleCallbackRequest' ] );
	}

    private function registerContainers(): void
    {
        $licenseApiFactory = new LicenseApiClientFactory();
        Container::add(LicenseApiClient::class, $licenseApiFactory->make());
        Container::addBulk([
            ChannelLabelService::class,
            ChannelLabelRepository::class,
        ]);
    }

    /**
     * @return void
     */
    private function registerUserContext(): void
    {
        $userFactory = new UserFactory();

        Container::add(UserContext::class, $userFactory->make());
    }
}
