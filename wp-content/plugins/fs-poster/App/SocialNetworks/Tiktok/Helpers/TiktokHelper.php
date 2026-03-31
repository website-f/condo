<?php

namespace FSPoster\App\SocialNetworks\Tiktok\Helpers;

use Exception;
use FSPoster\App\Libraries\PHPImage\PHPImage;
use InvalidArgumentException;

class TiktokHelper
{


	const MAX_SIZE_PHOTO = 1080;

	public static array $recycle_bin = [];


    /**
     * @throws Exception
     */
    public static function imageForFeed ( $photo )
    {
		$result = @getimagesize( $photo );

		if ( $result === false )
			throw new InvalidArgumentException( sprintf( 'The photo file "%s" is not a valid image.', $photo ) );

		$width  = $result[0];
		$height = $result[1];

        if ( $width <= self::MAX_SIZE_PHOTO && $height <= self::MAX_SIZE_PHOTO )
            return false;

		$newWidth  = $width;
		$newHeight = $height;

        if ( $newWidth > self::MAX_SIZE_PHOTO ) {
            $newHeight = (int)((self::MAX_SIZE_PHOTO / $newWidth) * $newHeight);
            $newWidth  = self::MAX_SIZE_PHOTO;
        }
        else if ( $newHeight > self::MAX_SIZE_PHOTO ) {
            $newWidth = (int)((self::MAX_SIZE_PHOTO / $newHeight) * $newWidth);
            $newHeight  = self::MAX_SIZE_PHOTO;
        }

		$image = new PHPImage();
		$image->initialiseCanvas( $newWidth, $newHeight, 'img', [ 255, 255, 255, 0 ] );

		$image->draw( $photo, '50%', '50%', $newWidth, $newHeight );

		$newFileName = self::randomPathAndUrl();

		$image->setOutput( 'jpg' )->save( $newFileName['path'] );

		return [
			'width'  => $newWidth,
			'height' => $newHeight,
			'path'   => $newFileName['path'],
			'url'    => $newFileName['url']
		];
	}


	public static function moveToTrash ( $filePath )
	{
		self::$recycle_bin[] = $filePath;
	}

    /**
     * @throws Exception
     * @return array{path: string, url: string}
     */
    private static function randomPathAndUrl(bool $isImage = true): array
    {
        $ext = $isImage ? '.jpg' : '.mp4';

        $wpUploadDir = wp_upload_dir();

        if(empty($wpUploadDir['basedir']) || empty($wpUploadDir['baseurl']))
        {
            throw new Exception(fsp__('Wordpress upload directory is not writeable to save processed media files'));
        }

        $dirPath = $wpUploadDir['basedir'] . DIRECTORY_SEPARATOR . FSP_PLUGIN_SLUG . DIRECTORY_SEPARATOR . 'tiktok';

        if(function_exists('mkdir') && !file_exists($dirPath))
        {
            $madeDir = mkdir($dirPath, 0777, true);

             if(!$madeDir)
             {
                 throw new Exception(fsp__('Failed to create upload directory'));
             }
        }

        $relativePath = [FSP_PLUGIN_SLUG, 'tiktok', uniqid() . $ext];

        self::$recycle_bin[] = implode(DIRECTORY_SEPARATOR, [$wpUploadDir['basedir'], ...$relativePath]);

        return [
            'path' => implode(DIRECTORY_SEPARATOR, [$wpUploadDir['basedir'], ...$relativePath]),
            'url'  => implode('/', [$wpUploadDir['baseurl'], ...$relativePath]),
        ];
    }

}