<?php
declare(strict_types=1);

namespace Cundd\Noshi\Utilities;

use Closure;
use LogicException;

/**
 * Utility class for accessing object properties
 */
class ObjectUtility
{
    /**
     * Returns the value for the key path of the given object
     *
     * @param string       $keyPath Key path of the property to fetch
     * @param object|array $object  Source to fetch the data from
     * @param mixed        $default An optional default value to return if the path could not be resolved. If a callback is passed, it's return value is used
     * @return mixed
     * @throws LogicException if the given key path is no string
     */
    static public function valueForKeyPathOfObject($keyPath, $object, $default = null)
    {
        $keyPathParts = explode('.', $keyPath);
        $keyPathPartsLength = count($keyPathParts);
        $currentValue = $object;

        if (!is_string($keyPath)) {
            throw new LogicException(
                'Given key path is not of type string (maybe arguments are ordered incorrect)',
                1395484136
            );
        }

        for ($i = 0; $i < $keyPathPartsLength; $i++) {
            $key = $keyPathParts[$i];
            $accessorMethod = 'get' . ucfirst($key);

            switch (true) {
                // Current value is an array
                case is_array($currentValue) && isset($currentValue[$key]):
                    $currentValue = $currentValue[$key];
                    break;

                // Current value is an object
                case is_object($currentValue):
                    if (method_exists($currentValue, $accessorMethod)) { // Getter method
                        $currentValue = $currentValue->$accessorMethod();
                    } elseif (method_exists($currentValue, 'get')) { // General "get" method
                        $currentValue = $currentValue->get($key);
                    } else {
                        if (in_array($key, get_object_vars($currentValue))) { // Direct access
                            $currentValue = $currentValue->$key;
                        } else {
                            $currentValue = null;
                        }
                    }
                    break;

                default:
                    $currentValue = null;
            }

            if ($currentValue === null) {
                break;
            }
        }

        if ($i !== $keyPathPartsLength && func_num_args() > 2) {
            if (is_object($default) && ($default instanceof Closure)) {
                $currentValue = $default();
            } else {
                $currentValue = $default;
            }
        }

        return $currentValue;
    }
}
