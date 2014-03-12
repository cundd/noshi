<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 12.03.14
 * Time: 22:09
 */

namespace Cundd\Noshi\Utilities;


class DebugUtility {
	/**
	 * Print debug information about the given values (arg0, arg1, ... argN)
	 *
	 * @param $variable
	 */
	static public function debug($variable){
		echo PHP_EOL;
		echo '<pre>';

		$variables = func_get_args();
		foreach ($variables as $variable) {
			var_dump($variable);
			echo PHP_EOL;
		}


		echo '</pre>';
		echo PHP_EOL;
	}
} 