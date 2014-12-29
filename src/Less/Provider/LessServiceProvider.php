<?php

namespace Less\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Create a LESS service provider to generate CSS file from LESS files
 */
class LessServiceProvider implements ServiceProviderInterface
{
	/**
	 * Value for classic CSS generated from LESS source files.
	 *
	 * @var string
	 */
	const FORMATTER_CLASSIC = 'classic';

	/**
	 * Value for compressed CSS generated from LESS source files.
	 *
	 * @var string
	 */
	const FORMATTER_COMPRESSED = 'compressed';


	public function register(Application $app) 
	{
	}


	public function boot(Application $app) 
	{

		// Validate this params.
		$this->validate($app);

		// Define default formatter if not already set.
		$formatter  = isset($app['less.formatter']) ? $app['less.formatter'] : self::FORMATTER_CLASSIC;
		$sourcesDir = $app['less.source_dir'];
		$cacheDir   = $app['less.cache_dir'];
		$targetDir  = $app['less.target_dir'];
		
		$cacheContents = array();

		foreach ($sourcesDir as $sourceDir) {

			$files = scandir($sourceDir);			
			foreach ($files as $file) {
				
				// if less file
				if (substr($file, -5) === '.less') {

					$cache = $cacheDir.$this->before('.less', $file).'.css.cache';
					// if file cached
					if (file_exists($cache)) {
						array_push($cacheContents, ['dir' => $sourceDir, 'name' => $file, 'file' => unserialize(file_get_contents($cache))]);
					}
					else {
						array_push($cacheContents, ['dir' => $sourceDir, 'name' => $file, 'file' => $sourceDir.$file]);
					}
				}
			}
		}

		$handle = new \lessc();
		$handle->setFormatter($formatter);

		foreach ($cacheContents as $cacheContent) {

			$newCache = $handle->cachedCompile($cacheContent['file']);

			if (!is_array($cacheContent['file']) || $newCache["updated"] > $cacheContent['file']["updated"]) {

				$target = $targetDir.$this->before('.less', $cacheContent['name']).'.css';
				$cache = $cacheDir.$this->before('.less', $cacheContent['name']).'.css.cache';

				// Write cache file
				file_put_contents($cache, serialize($newCache));
				// Write CSS file
				file_put_contents($target, $newCache['compiled']);
				// Change CSS permisions
				if(isset($app['less.target_mode'])){
					chmod($target, $app['less.target_mode']);
				}
			}
		}
	}


	/**
	 * Check if is required to recompile LESS file.
	 *
	 * @param string $source
	 *   File to compile (if required)
	 *
	 * @param string $target
	 *   Destination file for parsed LESS
	 *
	 * @return bool
	 *   Indicate if LESS file must be parsed
	 */
	private function targetNeedsRecompile($source, $target)
	{
		if (!file_exists($target)) {
			return true;
		}

		$sourceDir   = dirname($source);
		$targetMtime = filemtime($target);
		foreach (new \DirectoryIterator($sourceDir) as $lessFile) {
			/** @var $lessFile \DirectoryIterator */
			if ($lessFile->isFile() && substr($lessFile->getFilename(), -5) === '.less') {
				if ($lessFile->getMTime() > $targetMtime) {
					return true;
				}
			}
		}
		return false;
	}


	/**
	 * Validate application settings.
	 *
	 * @param \Silex\Application $app
	 *   Application to validate
	 *
	 * @throws \Exception
	 *   If some params is not valid throw exception.
	 */
	private function validate(Application $app) 
	{

		// Params must be defined.
		if (!isset($app['less.source_dir'], $app['less.target_dir'], $app['less.cache_dir'])) {
			throw new \Exception("Application['less.source_dir'], ['less.target_dir'] and ['less.cache_dir'] must be defined");
		}

		// Destination directory must be writable.
		$targetDir = dirname($app['less.target_dir']);
		if (!is_writable($targetDir)) {
			throw new \Exception("Target file directory \"$targetDir\" is not writable");
		}

		// Cache directory must be writable.
		$cacheDir = $app['less.cache_dir'];
		if (!is_writable($cacheDir)) {
			throw new \Exception("Cache file directory \"$cacheDir\" is not writable");
		}

		// Validate formatter type.
		if (isset($app['less.formatter']) && !in_array($app['less.formatter'], array(self::FORMATTER_CLASSIC, self::FORMATTER_COMPRESSED))) {
			throw new \Exception("Application['less.formatter'] can be 'classic' or 'compressed'");
		}
	}


	/**
	 * Return a portion of string after a delimiter.
	 *
	 * @param string $str
	 *   delimiter
	 *
	 * @param string $inthat
	 *   input string
	 *
	 * @return string
	 *   portion of string found, false if nothing
	 */
	private function after($str, $inthat)
    {
        if (!is_bool(strpos($inthat, $str)))
        return substr($inthat, strpos($inthat,$str) + strlen($str));
    }


	/**
	 * Return a portion of string before a delimiter.
	 *
	 * @param string $str
	 *   delimiter
	 *
	 * @param string $inthat
	 *   input string
	 *
	 * @return string
	 *   portion of string found, false if nothing
	 */
	private function before($str, $inthat)
    {
        return substr($inthat, 0, strpos($inthat, $str));
    }


	/**
	 * Return a portion of string between delimiters.
	 *
	 * @param string $after
	 *   delimiter
	 *
	 * @param string $before
	 *   delimiter
	 *
	 * @param string $inthat
	 *   input string
	 *
	 * @return string
	 *   portion of string found, false if nothing
	 */
    private function between($after, $before, $inthat)
    {
        return $this->before($before, $this->after($after, $inthat));
    }
}
