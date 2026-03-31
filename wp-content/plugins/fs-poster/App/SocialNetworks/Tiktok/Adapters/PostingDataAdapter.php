<?php

namespace FSPoster\App\SocialNetworks\Tiktok\Adapters;

use FSPoster\App\Providers\Helpers\WPPostThumbnail;
use FSPoster\App\Providers\Schedules\ScheduleObject;
use FSPoster\App\Providers\Schedules\ScheduleShareException;
use FSPoster\App\SocialNetworks\Tiktok\Api\PostingData;
use FSPoster\App\SocialNetworks\Tiktok\Helpers\TiktokHelper;

class PostingDataAdapter
{

	private ScheduleObject $scheduleObj;

	public function __construct( ScheduleObject $scheduleObj )
	{
		$this->scheduleObj = $scheduleObj;
	}

    public function __destruct()
    {
        foreach ( TiktokHelper::$recycle_bin as $image )
        {
            if( file_exists( $image ) )
                unlink( $image );
        }
    }

	/**
	 * @return PostingData
	 */
	public function getPostingData (): PostingData
	{
		$postingData = new PostingData();

        $postingData->title = $this->getPostingDataTitle();
		$postingData->description = $this->getPostingDataDescription();
		$postingData->uploadMedia = $this->getPostingDataUploadMedia();
		$postingData->disableComment = $this->getPostingDataDisableComment();
		$postingData->disableStitch = $this->getPostingDataDisableStitch();
		$postingData->disableDuet = $this->getPostingDataDisableDuet();
		$postingData->privacyLevel = $this->getPostingDataPrivacyLevel();
		$postingData->autoAddMusicToPhoto = $this->getPostingDataAutoAddMusicToPhoto();
        $postingData->promotionalContentType = $this->getPostingDataPromotionalContentType();

        if ( ! $this->scheduleObj->readOnlyMode && empty( $postingData->uploadMedia ) )
            throw new ScheduleShareException( fsp__( 'An image/video is required to share a post on Tiktok.' ) );

		return apply_filters( 'fsp_schedule_posting_data', $postingData, $this->scheduleObj );
	}

    public function getPostingDataTitle()
    {
        $scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;

        $title = $this->scheduleObj->replaceShortCodes( $scheduleData->photo_title ?? '', !$this->scheduleObj->readOnlyMode );

        $title = strip_tags( $title );
        return str_replace( [ '&nbsp;', "\r\n" ], [ ' ', "\n" ], $title );
    }

    public function getPostingDataDescription()
    {
        $scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;

        $description = $this->scheduleObj->replaceShortCodes( $scheduleData->post_content ?? '', !$this->scheduleObj->readOnlyMode );

        $description = strip_tags( $description );
        $description = str_replace( [ '&nbsp;', "\r\n" ], [ ' ', "\n" ], $description );

        return apply_filters( 'fsp_schedule_post_content', $description, $this->scheduleObj );
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

				$path = WPPostThumbnail::getOrCreateImagePath( $mediaID, $this->scheduleObj->readOnlyMode );
				$url = wp_get_attachment_url( $mediaID );
				$mimeType = get_post_mime_type( $mediaID );

				if( empty( $path ) )
					continue;

				$mediaListToUpload[] = [
					'id'    =>  $mediaID,
					'type'  =>  strpos( $mimeType, 'video' ) !== false ? 'video' : 'image',
					'mime_type'  =>  $mimeType,
					'path'  =>  $path,
					'url'   =>  $url
				];
			}
		}

        $mediaListToUpload = apply_filters( 'fsp_schedule_media_list_to_upload', $mediaListToUpload, $this->scheduleObj );

        return $this->cropMedia($mediaListToUpload);
	}

    private function cropMedia( array $mediaListToUpload ): array
    {
        if ( $this->scheduleObj->readOnlyMode || empty( $mediaListToUpload ) ) {
            return $mediaListToUpload;
        }

        foreach ( $mediaListToUpload AS $i => $media )
        {
            if( $media['type'] === 'image' )
            {
                try
                {
                    $newMedia = TiktokHelper::imageForFeed( $media['path'] );
                }
                catch ( \Exception $e )
                {
                    throw new ScheduleShareException( $e->getMessage() );
                }

                if ( $newMedia !== false )
                {
                    $mediaListToUpload[$i] = $newMedia;
                    $mediaListToUpload[$i]['id'] = $media['id'];
                    $mediaListToUpload[$i]['type'] = $media['type'];
                    $mediaListToUpload[$i]['original_url'] = $media['url'];
                    $mediaListToUpload[$i]['original_path'] = $media['path'];
                }
            }
        }

        return $mediaListToUpload;
    }

	public function getPostingDataDisableComment()
	{
		$scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;

		return (bool)($scheduleData->disable_comment ?? false);
	}

	public function getPostingDataDisableStitch()
	{
		$scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;

		return (bool)($scheduleData->disable_stitch ?? false);
	}

	public function getPostingDataDisableDuet()
	{
		$scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;

		return (bool)($scheduleData->disable_duet ?? false);
	}

	public function getPostingDataPrivacyLevel()
	{
		$scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;

		$allowedPrivacyLevels = [
			'PUBLIC_TO_EVERYONE',
			'MUTUAL_FOLLOW_FRIENDS',
			'FOLLOWER_OF_CREATOR',
			'SELF_ONLY'
		];

		$pLevel = $scheduleData->privacy_level ?? $allowedPrivacyLevels[0];

		return in_array( $pLevel, $allowedPrivacyLevels ) ? $pLevel : $allowedPrivacyLevels[0];
	}

	public function getPostingDataAutoAddMusicToPhoto()
	{
		$scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;

		return (bool)($scheduleData->auto_add_music_to_photo ?? true);
	}

    public function getPostingDataPromotionalContentType()
    {
        $scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;
        return $scheduleData->promotional_content_type ?: [];
    }

}