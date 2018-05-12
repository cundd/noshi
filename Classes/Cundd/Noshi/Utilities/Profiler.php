<?php

namespace Cundd\Noshi\Utilities;

/**
 * Profiler
 *
 * @package Cundd\Noshi\Utilities
 */
class Profiler
{
    /**
     * Start time of the profiler
     *
     * @var float
     */
    static protected $startTime;

    /**
     * URI of the stream to write the profiler information to
     *
     * @var string
     */
    static protected $streamUri = 'php://stdout';

    /**
     * Show the processing time
     *
     * @param string $message Optional profiling message
     */
    static public function profile($message = null)
    {
        static $fileHandle = null;

        // Make sure the start time is defined
        if (!static::$startTime) {
            static::$startTime = microtime(true);
        }

        // Make sure the file handle exists
        if (!$fileHandle) {
            $fileHandle = fopen(static::$streamUri, 'w');
        }

        $output = sprintf('Profiler: %.4f', microtime(true) - static::$startTime);
        if ($message) {
            $output .= ' - ' . $message;
        }
        $output .= PHP_EOL;
        fwrite($fileHandle, $output);
    }

    /**
     * Show the processing time
     *
     * @return bool Returns if the profiler just has been started
     */
    static public function start()
    {
        if (!static::$startTime) {
            static::$startTime = microtime(true);

            return true;
        }

        return false;
    }
} 