<?php

namespace FSPoster\App\SocialNetworks\Bluesky\Adapters;

use FSPoster\App\Providers\Helpers\WPPostThumbnail;
use FSPoster\App\Providers\Schedules\ScheduleObject;
use FSPoster\App\SocialNetworks\Bluesky\Api\PostingData;

class PostingDataAdapter
{
    private ScheduleObject $scheduleObj;

    public function __construct( ScheduleObject $scheduleObj )
    {
        $this->scheduleObj = $scheduleObj;
    }

    /**
     * @throws \Exception
     */
    public function getPostingData() : PostingData
    {
        $postingData = new PostingData();

        $postingData->message = $this->getPostingDataMessage();
        $postingData->uploadMedia = $this->getPostingDataUploadMedia();
        $postingData->firstComment = $this->getPostingDataFirstComment();
        $postingData->link = $this->getPostingDataLink();

        $videoCount = 0;
        $imageCount = 0;

        foreach ( $postingData->uploadMedia as $media ) {
            if ( $media['type'] === 'video' ) {
                $videoCount++;
            } else {
                $imageCount++;
            }
        }

        if ($imageCount > 4 || $videoCount > 1 || ($videoCount === 1 && $imageCount > 0)) {
            throw new \Exception(fsp__("Can only upload either 1 video or up to 4 images"));
        }

        return apply_filters( 'fsp_schedule_posting_data', $postingData, $this->scheduleObj );
    }

    public function getPostingDataMessage()
    {
        $scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;

        $message = $this->scheduleObj->replaceShortCodes( $scheduleData->post_content ?? '', !$this->scheduleObj->readOnlyMode );

        $message = strip_tags( $message );
        $message = str_replace( [ '&nbsp;', "\r\n" ], [ ' ', "\n" ], $message );
        $message = preg_replace( "/\n\s*\n\s*/", "\n\n", $message );

        if (function_exists('grapheme_substr')) {
            $message = grapheme_substr($message, 0, 300);
        } else {
            $message = mb_substr($message, 0, 300);
        }

        return apply_filters( 'fsp_schedule_post_content', $message, $this->scheduleObj );
    }

    public function getPostingDataUploadMedia()
    {
        $scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;
        $mediaListToUpload = [];

        if( $scheduleData->upload_media )
        {
            if( $scheduleData->upload_media_type === 'featured_image' )
                $mediaIDs = [ $this->scheduleObj->getPostThumbnailID() ];
            else if( $scheduleData->upload_media_type === 'all_images' )
                $mediaIDs = $this->scheduleObj->getPostAllAttachedImagesID();
            else
                $mediaIDs = $scheduleData->media_list_to_upload ?? [];

            foreach ( $mediaIDs AS $mediaID )
            {
                if( ! ( $mediaID > 0 ) )
                    continue;

                $name = basename( get_attached_file( $mediaID ) );
                $path = WPPostThumbnail::getOrCreateImagePath( $mediaID, $this->scheduleObj->readOnlyMode );
                $url = wp_get_attachment_url( $mediaID );
                $mimeType = get_post_mime_type( $mediaID );
                $type = strpos( $mimeType, 'video' ) !== false ? 'video' : 'image';
                $alt = get_post_meta( $mediaID, '_wp_attachment_image_alt', true );

                if( empty( $path ) )
                    continue;

                $mediaListToUpload[] = [
                    'id' =>  $mediaID,
                    'name' => $name,
                    'type' =>  $type,
                    'mimeType' => $mimeType,
                    'path' => $path,
                    'url' => $url,
                    'alt' => $alt
                ];
            }
        }

        return apply_filters( 'fsp_schedule_media_list_to_upload', $mediaListToUpload, $this->scheduleObj );
    }

    public function getPostingDataFirstComment()
    {
        $scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;

        $firstComment = $this->scheduleObj->replaceShortCodes( $scheduleData->first_comment ?? '', !$this->scheduleObj->readOnlyMode );
        $firstComment = strip_tags( $firstComment );
        $firstComment = str_replace( [ '&nbsp;', "\r\n" ], [ ' ', "\n" ], $firstComment );

        $firstComment = grapheme_substr($firstComment, 0, 300);

        return $firstComment;
    }

    public function getPostingDataLink()
    {
        $scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;

        $link = '';

        if( $scheduleData->attach_link )
        {
            if( ! empty( $scheduleData->custom_link ) )
                $link = $scheduleData->custom_link;
            else
                $link = $this->scheduleObj->getPostLink();
        }

        return apply_filters( 'fsp_schedule_post_link', $link, $this->scheduleObj );
    }

}