<?php

namespace FSPoster\App\SocialNetworks\Flickr\Adapters;

use FSPoster\App\Providers\Core\Settings;
use FSPoster\App\Providers\Helpers\WPPostThumbnail;
use FSPoster\App\Providers\Schedules\ScheduleObject;
use FSPoster\App\Providers\Schedules\ScheduleShareException;
use FSPoster\App\SocialNetworks\Flickr\Api\PostingData;

class PostingDataAdapter
{

	private ScheduleObject $scheduleObj;

	public function __construct ( ScheduleObject $scheduleObj )
	{
		$this->scheduleObj = $scheduleObj;
	}

	public function getPostingData (): PostingData
	{
		$postingData = new PostingData();

		$postingData->title       = $this->getPostingDataTitle();
		$postingData->description = $this->getPostingDataDescription();
		$postingData->tags        = $this->getPostingDataTags();
		$postingData->uploadMedia = $this->getPostingDataUploadMedia();
		$postingData->albumId     = $this->getPostingDataAlbumId();
		$postingData->firstComment = $this->getPostingDataFirstComment();

		$this->applyPrivacySettings( $postingData );

		if ( empty( $postingData->uploadMedia ) && ! $this->scheduleObj->readOnlyMode )
			throw new ScheduleShareException( fsp__( 'An image or video is required to post on Flickr' ) );

		return apply_filters( 'fsp_schedule_posting_data', $postingData, $this->scheduleObj );
	}

	public function getPostingDataTitle (): string
	{
		$scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;

		$title = $this->scheduleObj->replaceShortCodes( $scheduleData->post_title ?? '', ! $this->scheduleObj->readOnlyMode );

		$title = strip_tags( $title );
		$title = str_replace( [ '&nbsp;', "\r\n" ], [ ' ', "\n" ], $title );

		return $title;
	}

	public function getPostingDataDescription (): string
	{
		$scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;

		$message = $this->scheduleObj->replaceShortCodes( $scheduleData->post_content ?? '', ! $this->scheduleObj->readOnlyMode );

		$message = strip_tags( $message );
		$message = str_replace( [ '&nbsp;', "\r\n" ], [ ' ', "\n" ], $message );

		return apply_filters( 'fsp_schedule_post_content', $message, $this->scheduleObj );
	}

	public function getPostingDataTags (): string
	{
		$scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;

		if ( empty( $scheduleData->send_tags ) )
			return '';

		$tags = array_column( $this->scheduleObj->getWpPostTerms( 'post_tag' ), 'name' );

		// Flickr tags are space-separated; multi-word tags should be quoted
		$formattedTags = [];

		foreach ( $tags as $tag )
		{
			if ( strpos( $tag, ' ' ) !== false )
				$formattedTags[] = '"' . $tag . '"';
			else
				$formattedTags[] = $tag;
		}

		return implode( ' ', $formattedTags );
	}

	public function getPostingDataAlbumId (): string
	{
		$scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;

		return $scheduleData->album_id ?? '';
	}

	public function getPostingDataFirstComment (): string
	{
		$scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;

		if ( empty( $scheduleData->first_comment ) )
			return '';

		$comment = $this->scheduleObj->replaceShortCodes( $scheduleData->first_comment, ! $this->scheduleObj->readOnlyMode );

		return strip_tags( $comment );
	}

	private function applyPrivacySettings ( PostingData $postingData ): void
	{
		$scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;
		$privacy = $scheduleData->privacy ?? Settings::get( 'flickr_privacy', 'public' );

		switch ( $privacy )
		{
			case 'friends':
				$postingData->isPublic = 0;
				$postingData->isFriend = 1;
				$postingData->isFamily = 0;
				break;
			case 'family':
				$postingData->isPublic = 0;
				$postingData->isFriend = 0;
				$postingData->isFamily = 1;
				break;
			case 'friends_and_family':
				$postingData->isPublic = 0;
				$postingData->isFriend = 1;
				$postingData->isFamily = 1;
				break;
			case 'private':
				$postingData->isPublic = 0;
				$postingData->isFriend = 0;
				$postingData->isFamily = 0;
				break;
			default: // public
				$postingData->isPublic = 1;
				$postingData->isFriend = 0;
				$postingData->isFamily = 0;
				break;
		}
	}

	public function getPostingDataUploadMedia (): array
	{
		$scheduleData = $this->scheduleObj->getSchedule()->customization_data_obj;
		$mediaListToUpload = [];

		$uploadMediaType = $scheduleData->upload_media_type ?? 'featured_image';

		if ( $uploadMediaType === 'featured_image' )
		{
			$mediaIDs = [ $this->scheduleObj->getPostThumbnailID() ];
		}
		else if ( $uploadMediaType === 'all_images' )
		{
			$mediaIDs = $this->scheduleObj->getPostAllAttachedImagesID();
			$mediaIDs = array_slice( $mediaIDs, 0, 1 ); // Flickr uploads one photo per post
		}
		else
		{
			$mediaIDs = $scheduleData->media_list_to_upload ?? [];
		}

		foreach ( $mediaIDs as $mediaID )
		{
			if ( ! ( $mediaID > 0 ) )
				continue;

			$path     = WPPostThumbnail::getOrCreateImagePath( $mediaID, $this->scheduleObj->readOnlyMode );
			$url      = wp_get_attachment_url( $mediaID );
			$mimeType = get_post_mime_type( $mediaID );
			$isImage  = strpos( $mimeType, 'image' ) !== false;
			$isVideo  = strpos( $mimeType, 'video' ) !== false;

			if ( empty( $path ) || ( ! $isImage && ! $isVideo ) )
				continue;

			$mediaListToUpload[] = [
				'id'   => $mediaID,
				'type' => $isVideo ? 'video' : 'image',
				'path' => $path,
				'url'  => $url,
			];
		}

		return apply_filters( 'fsp_schedule_media_list_to_upload', $mediaListToUpload, $this->scheduleObj );
	}
}
