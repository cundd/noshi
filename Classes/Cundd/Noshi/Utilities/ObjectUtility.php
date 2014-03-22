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
	 * @param string       $keyPath Key path of the property to fetch
	 * @param object|array $object  Source to fetch the data from
	 * @param mixed        $default An optional default value to return if the path could not be resolved. If a callback is passed, it's return value is used
	 * @return mixed
	 */
	static public function valueForKeyPathOfObject($keyPath, $object, $default = NULL) {
		$i = 0;
		$keyPathParts = explode('.', $keyPath);
		$keyPathPartsLength = count($keyPathParts);
		$currentValue = $object;

		for ($i = 0; $i < $keyPathPartsLength; $i++) {
			$key = $keyPathParts[$i];
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
					$currentValue = NULL;
			}

			if ($currentValue === NULL) break;
		}

		if ($i !== $keyPathPartsLength && func_num_args() > 2) {
			if (is_object($default) && ($default instanceof \Closure)) {
				$currentValue = $default();
			} else {
				$currentValue = $default;
			}
		}
		return $currentValue;
	}
} 