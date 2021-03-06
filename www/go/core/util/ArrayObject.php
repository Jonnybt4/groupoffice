<?php
namespace go\core\util;

class ArrayObject extends \ArrayObject {
	
	/**
	 * Find the key in an array by a callable function.
	 * If the function returns true the key is returned
	 * 
	 * @example
	 * ```````````````````````````````````````````````````````````````````````````
	 * 
	 * $arr = [3, 5, 9];
	 * 
	 * $findGreatherThan = 4;
	 * 
	 * $arrayObject = new \go\core\util\ArrayObject($arr);
	 *	
	 * $key = $arrayObject->findKey(function($i) use ($findGreatherThan){
	 *		return $i >= $findGreatherThan;
	 * });	
	 * 
	 * //$key is 1 (value 5)
	 * ```````````````````````````````````````````````````````````````````````````
	 * 
	 * Note that if you want to evaluate false you need to use '===' because this 
	 * may return key 0 which evaluates to false too.
	 * 
	 * @param \go\core\util\callable $fn
	 * @return mixed
	 */
	public function findKeyBy(callable $fn) {		
		foreach($this as $key => $value) {
			if(call_user_func($fn, $value)) {
				return $key;
			}
		}
		
		return false;
	}
	
	/**
	 * Merge array recursively. 
	 * 
	 * array_merge_recursive from php does not handle string elements right. 
	 * It does not overwrite them but it creates unwanted sub arrays.
	 * 
	 * @param array $arr
	 * @return self
	 */
	public function mergeRecurive(array $arr) {
		foreach ($arr as $key => $value) {
			if (is_array($value) && isset($this[$key])) {
				$this[$key] = self::mergeRecurive($this[$key], $value);
			} else {
				$this[$key] = $value;
			}
		}

		return $this;
	}
}
