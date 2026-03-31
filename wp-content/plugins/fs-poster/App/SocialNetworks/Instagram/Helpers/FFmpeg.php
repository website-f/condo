<?php

namespace FSPoster\App\SocialNetworks\Instagram\Helpers;

use Exception;
use FSPoster\App\Providers\Core\Settings;
use FSPoster\Symfony\Component\Process\Process;
use RuntimeException;

class FFmpeg
{

	public static    $defaultTimeout = 600;
	private static   $videoDetails = [];
	protected static $_instance   = null;

	protected        $_ffmpegBinary;
	protected        $_ffprobeBinary;
	protected        $_hasLibFdkAac;


    public function setFFmpegBinary ( $ffmpegBinary )
    {
        $this->_ffmpegBinary = $ffmpegBinary;
    }

    public function setFFprobeBinary ( $ffprobeBinary )
    {
        $this->_ffprobeBinary = $ffprobeBinary;
    }

	public function run ( array $command )
	{
		$process = $this->runAsync( $command );

		try
		{
			$exitCode = $process->wait();
		}
		catch ( Exception $e )
		{
			throw new RuntimeException( sprintf( 'Failed to run the ffmpeg binary: %s', $e->getMessage() ) );
		}

		if ( $exitCode )
		{
			$errors   = preg_replace( '#[\r\n]+#', '"], ["', trim( $process->getErrorOutput() ) );
			$errorMsg = sprintf( 'FFmpeg Errors: ["%s"], Command: "%s".', $errors, implode(' ', $command) );

			throw new RuntimeException( $errorMsg, $exitCode );
		}

		return preg_split( '#[\r\n]+#', $process->getOutput(), null, PREG_SPLIT_NO_EMPTY );
	}

	public function runAsync ( array $command ) : Process
    {
		$process = new Process( [
            $this->_ffmpegBinary,
            '-v',
            'error',
            ...$command,
        ] );
		if ( is_int( self::$defaultTimeout ) && self::$defaultTimeout > 60 )
		{
			$process->setTimeout( self::$defaultTimeout );
		}
		$process->start();

		return $process;
	}

	public function version ()
	{
		return $this->run( ['-version'] )[ 0 ];
	}

	public function hasLibFdkAac ()
	{
		if ( $this->_hasLibFdkAac === null )
		{
			$this->_hasLibFdkAac = $this->_hasAudioEncoder( 'libfdk_aac' );
		}

		return $this->_hasLibFdkAac;
	}

	protected function _hasAudioEncoder ( $encoder )
	{
		try
		{
			$this->run( ['-f', 'lavfi',  '-i',  'anullsrc=channel_layout=stereo:sample_rate=44100', '-c:a', static::escape($encoder), '-t', 1, '-f', 'null', '-'] );

			return true;
		}
		catch ( RuntimeException $e )
		{
			return false;
		}
	}

	public static function factory ()
	{
        if ( is_null( self::$_instance ) )
        {
            $ffmpegBin = self::getFfmpegBinary();
            $ffprobeBin = self::getFfprobeBinary();

            if ( self::checkFFmpeg( $ffmpegBin ) && self::checkFFprobe( $ffprobeBin ) ) {
                self::$_instance = new static();
                self::$_instance->setFFmpegBinary( $ffmpegBin );
                self::$_instance->setFFprobeBinary( $ffprobeBin );
            }
            else {
                throw new RuntimeException( fsp__( 'For sharing videos on Instagram, you have to install the FFmpeg library on your server and configure executables\' path.' ) );
            }
        }

        return self::$_instance;
	}

    protected static function getFfmpegBinary (): string
    {
        $ffmpegCustomPath = Settings::get( 'instagram_ffmpeg_path', '' );

        if ( ! empty( $ffmpegCustomPath ) )
            return $ffmpegCustomPath;
        else if( defined( 'PHP_WINDOWS_VERSION_MAJOR' ) )
            return 'ffmpeg.exe';
        else
            return 'ffmpeg';
    }

    protected static function getFfprobeBinary (): string
    {
        $ffprobeCustomPath = Settings::get( 'instagram_ffprobe_path', '' );

        if ( ! empty( $ffprobeCustomPath ) )
            return $ffprobeCustomPath;
        else if( defined( 'PHP_WINDOWS_VERSION_MAJOR' ) )
            return 'ffprobe.exe';
        else
            return 'ffprobe';
    }

	public static function escape ( $arg, $meta = true )
	{
		if ( ! defined( 'PHP_WINDOWS_VERSION_BUILD' ) )
		{
			return escapeshellarg( $arg );
		}

		$quote = strpbrk( $arg, " \t" ) !== false || $arg === '';
		$arg   = preg_replace( '/(\\\\*)"/', '$1$1\\"', $arg, -1, $dquotes );

		if ( $meta )
		{
			$meta = $dquotes || preg_match( '/%[^%]+%/', $arg );

			if ( ! $meta && ! $quote )
			{
				$quote = strpbrk( $arg, '^&|<>()' ) !== false;
			}
		}

		if ( $quote )
		{
			$arg = preg_replace( '/(\\\\*)$/', '$1$1', $arg );
			$arg = '"' . $arg . '"';
		}

		if ( $meta )
		{
			$arg = preg_replace( '/(["^&|<>()%])/', '^$1', $arg );
		}

		return $arg;
	}

    public static function checkFFmpeg ( $ffmpegBinary = null ): bool
    {
        if( $ffmpegBinary === null )
            $ffmpegBinary = self::getFfmpegBinary();

        if( function_exists( 'exec' ) ) {
            @exec( $ffmpegBinary . ' -version 2>&1', $output, $statusCode );
        } else {
            $statusCode = null;
        }

        if ( $statusCode === 0 ) {
            return true;
        }

        return false;
    }

    public static function checkFFprobe ( $ffprobeBinary = null ): bool
    {
        if ( $ffprobeBinary === null )
            $ffprobeBinary = self::getFfprobeBinary();

        if ( function_exists( 'exec' ) ) {
            @exec( $ffprobeBinary . ' -version 2>&1', $output, $statusCode );
        } else {
            $statusCode = null;
        }

        if ( $statusCode === 0 ) {
            return true;
        }

        return false;
    }

	public static function checkLibx264 () {
		if( function_exists( 'exec' ) ) {
			@exec( "ffmpeg -codecs 2>&1", $output, $statusCode );

			if ( $statusCode === 0 ) {
				$outputString = implode( "\n", $output );

				if ( strpos( $outputString, 'libx264' ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	public function videoDetails ( $filename )
	{
		if ( ! isset( static::$videoDetails[ md5( $filename ) ] ) )
		{
			$command = sprintf( '%s -v quiet -print_format json -show_format -show_streams %s', static::escape( $this->_ffprobeBinary ), static::escape( $filename ) );

			$jsonInfo    = @shell_exec( $command );
			$probeResult = @json_decode( $jsonInfo, true );

			if ( ! is_array( $probeResult ) || ! isset( $probeResult[ 'streams' ] ) || ! is_array( $probeResult[ 'streams' ] ) )
			{
				throw new RuntimeException( sprintf( 'FFprobe failed to detect any stream. Is "%s" a valid media file?', $filename ) );
			}

			$videoCodec = null;
			$width      = 0;
			$height     = 0;
			$duration   = 0;
			$audioCodec = null;

			foreach ( $probeResult[ 'streams' ] as $streamIdx => $streamInfo )
			{
				if ( ! isset( $streamInfo[ 'codec_type' ] ) )
				{
					continue;
				}

				switch ( $streamInfo[ 'codec_type' ] )
				{
					case 'video':
						$videoCodec = (string) $streamInfo[ 'codec_name' ];
						$width      = (int) $streamInfo[ 'width' ];
						$height     = (int) $streamInfo[ 'height' ];

						if ( isset( $streamInfo[ 'duration' ] ) )
						{
							$duration = (int) $streamInfo[ 'duration' ];
						}
						break;
					case 'audio':
						$audioCodec = (string) $streamInfo[ 'codec_name' ];
						break;
				}
			}

			if ( is_null( $duration ) && isset( $probeResult[ 'format' ][ 'duration' ] ) )
			{
				$duration = (int) $probeResult[ 'format' ][ 'duration' ];
			}

			if ( is_null( $duration ) )
			{
				throw new RuntimeException( sprintf( 'FFprobe failed to detect video duration. Is "%s" a valid video file?', $filename ) );
			}

			static::$videoDetails[ md5( $filename ) ] = array_merge( $probeResult, [
				'video_codec' => $videoCodec,
				'width'       => $width,
				'height'      => $height,
				'duration'    => $duration,
				'audio_codec' => $audioCodec,
			] );
		}

		return static::$videoDetails[ md5( $filename ) ];
	}
}
