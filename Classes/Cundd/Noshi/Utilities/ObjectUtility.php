<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 19.03.14
 * Time: 18:06
 */

namespace Cundd\Noshi\Utilities;

/**
 * Utility class for accessing object properties
 *
 * @package Cundd\Noshi\Utilities
 */
class ObjectUtility {
	/**
	 * Returns the value for the key path of the given object
	 *
	 * @param string $keyPath
	 * @param object|array $object
	 * @return mixed
	 */
	static public function valueForKeyPathOfObject($keyPath, $object) {
		$keyPathParts = explode('.', $keyPath);
		$currentValue = $object;

		foreach ($keyPathParts as $key) {
			$accessorMethod = 'get' . ucfirst($key);

			switch (TRUE) {
				// Current value is an array
				case is_array($currentValue) && isset($currentValue[$key]):
					$currentValue = $currentValue[$key];
					break;

				// Current value is an object
				case is_object($currentValue):

				case method_exists($currentValue, $accessorMethod):
					$currentValue = $currentValue->$accessorMethod();
					break;

				case property_exists($currentValue, $key) && isset($currentValue->$key):
					$publicProperties = get_object_vars($currentValue);
					if (in_array($key, $publicProperties)) {
						$currentValue = $currentValue->$key;
						break;
					}

				default:
				#	DebugUtility::debug('blöd', $key, gettype($currentValue), $currentValue);
					$currentValue = NULL;
			}

			#DebugUtility::debug($currentValue, $key);
			if ($currentValue === NULL) break;
		}
		return $currentValue;
	}
} 