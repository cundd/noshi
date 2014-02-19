<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 19/02/14
 * Time: 20:42
 */

namespace Cundd\Noshi;


class ConfigurationManager {
	/**
	 * @var Configuration
	 */
	static protected $sharedConfiguration;

	/**
	 * Returns the shared configuration
	 *
	 * @return Configuration
	 */
	static public function getConfiguration() {
		if (!self::$sharedConfiguration) {
			self::$sharedConfiguration = new Configuration();
		}
		return self::$sharedConfiguration;
	}
} 