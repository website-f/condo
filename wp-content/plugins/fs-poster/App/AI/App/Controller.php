<?php

namespace FSPoster\App\AI\App;

use Exception;
use FSPoster\App\Models\AILogs;
use FSPoster\App\Models\AITemplate;
use FSPoster\App\Models\Schedule;
use FSPoster\App\Providers\Core\RestRequest;
use FSPoster\App\Providers\Helpers\Date;
use FSPoster\App\Providers\Helpers\Helper;

class Controller
{

    /**
     * @throws \Exception
     */
    public static function saveTemplate ( RestRequest $request ): array
    {
        $id           = $request->param( 'id', '', RestRequest::TYPE_STRING );
        $title        = $request->require( 'title', RestRequest::TYPE_STRING, fsp__( 'Please enter a title' ) );
        $provider     = $request->require( 'provider', RestRequest::TYPE_STRING, fsp__( 'Please specify a valid AI provider' ) );
        $prompt       = $request->require( 'prompt', RestRequest::TYPE_STRING, fsp__( 'Please set a prompt' ) );
        $fallbackText = $request->param( 'fallback_text', '', RestRequest::TYPE_STRING );
        $model        = $request->require( 'ai_model', RestRequest::TYPE_STRING, fsp__( 'Please specify a model' ) );
        $type         = $request->require( 'type', RestRequest::TYPE_STRING, fsp__( 'Please select type' ), [ 'image', 'text' ] );
        $config       = $request->param( 'config', [], RestRequest::TYPE_ARRAY );

        $template = apply_filters( 'fsp_ai_save_template', [
            'id'            => $id,
            'title'         => $title,
            'provider'      => $provider,
            'prompt'        => $prompt,
            'fallback_text' => $fallbackText,
            'ai_model'      => $model,
            'type'          => $type,
            'config'        => $config,
            'created_by'    => get_current_user_id(),
            'blog_id'       => Helper::getBlogId(),
        ], $provider );

        if ( empty( $template[ 'id' ] ) )
        {
            $template[ 'config' ] = json_encode( $template[ 'config' ] );

            AITemplate::insert( $template );
        } else
        {
            $template[ 'config' ] = json_encode( $template[ 'config' ] );
            AITemplate::where( 'id', $template[ 'id' ] )->update( $template );
        }

        return [];
    }

    /**
     * @throws Exception
     */
    public static function get ( RestRequest $request )
    {
        $id = $request->param( 'id', 0, RestRequest::TYPE_INTEGER );

        $template = AITemplate::get( $id );

        if ( !$template )
        {
            throw new Exception( fsp__( 'Template not found' ) );
        }

        $template[ 'config' ] = $template->config_obj->toArray();

        unset( $template[ 'created_by' ] );
        unset( $template[ 'blog_id' ] );

        return [
            'template' => $template,
        ];
    }

    /**
     * @throws \Exception
     *
     * pagination sonradan gerekli olmadi, silmeli olduq
     */
    public static function listTemplates ( RestRequest $request ): array
    {
        $type = $request->param( 'type', '', RestRequest::TYPE_STRING, [ 'image', 'text' ] );

        $templates = new AITemplate();

        if ( !empty( $type ) )
        {
            $templates->where( 'type', $type );
        }

        $templates = $templates->fetchAll();

        return [
            'templates' => array_map( function ( $t )
            {
                $t[ 'id' ]     = (int)$t[ 'id' ];
                $t[ 'config' ] = json_decode( $t[ 'config' ], true );
                unset( $t[ 'blog_id' ] );
                unset( $t[ 'created_by' ] );
                return $t;
            }, $templates ),
        ];
    }

    /**
     * @throws \Exception
     */
    public static function deleteTemplates ( RestRequest $request ): array
    {
        $ids = $request->require( 'ids', RestRequest::TYPE_ARRAY, fsp__( 'Please select templates to delete' ) );

        AITemplate::where( 'id', 'in', $ids )->delete();

        return [];
    }

    public static function logs ( RestRequest $request ): array
    {
        $page = $request->param( 'page', 1, RestRequest::TYPE_INTEGER );
        $page = $page > 0 ? $page : 1;

        $result = AILogs::leftJoin( AITemplate::getTableName(), [], AILogs::getField( 'template_id' ), AITemplate::getField( 'id' ) )
            ->leftJoin( Schedule::getTableName(), [], AILogs::getField( 'schedule_id' ), Schedule::getField( 'id' ) )
            ->select( [
                AILogs::getField( 'created_at' ),
                AITemplate::getField( 'title' ) . ' as template_title',
                AITemplate::getField( 'prompt' ),
                AITemplate::getField( 'ai_model' ),
                Schedule::getField( 'id' ) . ' as schedule_id',
                Schedule::getField( 'group_id' ) . ' as schedule_group_id',
                AILogs::getField( 'status' ),
                AILogs::getField( 'raw_response' )
            ] );

        $logs = [];
        $count = $result->count();
        $result = $result->offset( ( $page - 1 ) * 10 )->limit( 10 );

        foreach ( $result->fetchAll() as $log ) {
            $logs[] = [
                'created_at' => !empty( $log->created_at ) ? Date::epoch( $log->created_at ) : null,
                'template_title' => $log->template_title,
                'prompt' => $log->prompt,
                'ai_model' => $log->ai_model,
                'schedule_id' => $log->schedule_id,
                'schedule_group_id' => $log->schedule_group_id,
                'status' => $log->status,
                'raw_response' => $log->raw_response
            ];
        }

        return [
            'count' => $count,
            'logs' => $logs
        ];
    }

    public static function deleteLogs ( RestRequest $request ): array
    {
        AILogs::delete();

        return [];
    }
}
