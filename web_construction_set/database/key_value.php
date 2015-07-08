<?php

namespace WebConstructionSet\Database;

interface KeyValue {
	/**
	 * Установит новую пару
	 * @param unknown $key
	 * @param unknown $value
	 * @return boolean
	 */
	public function set($key, $value);

	public function getValue($key);

	public function getKey($value);

	/**
	 * Удалить пару
	 * @param unknown $key
	 * @return boolean
	 */
	public function delete($key);
}

?>