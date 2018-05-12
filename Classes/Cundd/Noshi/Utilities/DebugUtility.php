<?php

namespace Cundd\Noshi\Utilities;


class DebugUtility
{
    /**
     * Print debug information about the given values (arg0, arg1, ... argN)
     *
     * @param $variable
     */
    static public function debug($variable)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $caller = $backtrace[1];

        echo PHP_EOL;
        echo '<pre class="noshi-debug"><code>';
        $variables = func_get_args();
        foreach ($variables as $variable) {
            var_dump($variable);
            echo PHP_EOL;
        }
        echo '</code>';

        // Debug info
        $file = $caller['file'];
        $line = $caller['line'];
        echo "<span class='noshi-debug-path' style='font-size:9px'><a href='file:$file'>$file @ $line</a></span>" . PHP_EOL;

        echo '</pre>';
        echo PHP_EOL;


    }

}