<?php

namespace FSPoster\App\Providers\Planners;

use FSPoster\App\Models\Planner;
use FSPoster\App\Providers\DB\DB;
use FSPoster\App\Providers\Helpers\Date;
use FSPoster\App\Providers\Helpers\Helper;
use FSPoster\App\Providers\Schedules\ScheduleService;

class PlannerService
{

    public static function sharePlanners ()
    {
        $nowDateTime = Date::dateTimeSQL();

        $planners = Planner::where( 'status', 'active' )->where( 'next_execute_at', '<=', $nowDateTime )->fetchAll();

        //prevent duplicates for interval schedules
        Planner::where( 'status', 'active' )->where( 'share_type', 'interval' )->where( 'next_execute_at', '<=', $nowDateTime )->update( [
            'next_execute_at' => DB::field( DB::raw( 'DATE_ADD(`next_execute_at`, INTERVAL ((TIMESTAMPDIFF(MINUTE, `next_execute_at`, %s) DIV (schedule_interval DIV 60) ) + 1) * (schedule_interval DIV 60) minute)', [ $nowDateTime ] ) ),
        ] );

        foreach ( $planners as $planner )
        {
            if ( $planner->share_type === 'weekly' )
            {
                $weeklyScheduleNextExecuteTime = PlannerHelper::weeklyNextExecuteTime( json_decode( $planner->weekly, true ) );

                Planner::where( 'id', $planner->id )->update( [ 'next_execute_at' => $weeklyScheduleNextExecuteTime ] );
            }

            if ( PlannerHelper::isSleepTime( $planner ) )
            {
                continue;
            }

            Helper::setBlogId( $planner->blog_id );

            $plannerSelectedPosts = empty( $planner->selected_posts ) ? [] : explode( ',', $planner->selected_posts );
            $filterQuery = PlannerHelper::plannerFilters( $planner );

            /* End post_sort */
            $getRandomPost = DB::DB()->get_row( "SELECT * FROM `" . DB::WPtable( 'posts', true ) . "` tb1 WHERE (post_status='publish' OR post_type='attachment') AND {$filterQuery} LIMIT 1", ARRAY_A );

            $postId = !empty( $getRandomPost['ID'] ) ? (int)$getRandomPost['ID'] : 0;

            if ( empty( $postId ) )
            {
                if ( !empty( $planner->selected_posts ) || $planner->sort_by !== 'old_to_new' )
                {
                    if ( $planner->repeating == '1' )
                    {
                        Planner::where( 'id', $planner->id )->update( [ 'shared_posts' => '' ] );
                    } else
                    {
                        Planner::where( 'id', $planner->id )->update( [ 'status' => 'finished' ] );
                    }
                }
            } else {
                $plannerShared = ScheduleService::createSchedulesFromPlanner( $planner, $postId );

                if ( $plannerShared ) {
                    $sharedPostsCount = empty( $planner->shared_posts ) ? 0 : count(explode( ',', $planner->shared_posts ));
                    $sharedPostsCount++;

					if ((string)$planner->repeating === '1' && count($plannerSelectedPosts) === $sharedPostsCount) {
                        Planner::where( 'id', $planner->id )->update( [ 'shared_posts' => '' ] );
					} else {
                        // burda evvel arraya push edib, sonra impplode edib update edirdik.
                        // Muhsterinin birinde 2k post_id var idi ve sql query chox uzun olurdu deye run olmurdu
                        // ona gore update-ni bu usula kechirdik.
                        Planner::where('id', $planner->id)->update([
                            'shared_posts' => DB::field(DB::raw("CONCAT_WS(',',NULLIF(`shared_posts`, ''), '$postId')")),
                        ]);
                    }
                } else {
                    Planner::where( 'id', $planner->id )->update( [ 'status' => 'paused' ] );
                }
            }

            Helper::resetBlogId();
        }
    }

}