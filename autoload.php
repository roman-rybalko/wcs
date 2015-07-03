<?php

/**
 * Загружает класс.
 * Путь к файлу: Path\NameSpace\ClassName -> path/name_space/class_name.php
 * @param string $classname
 */
function __autoload($classname) {
	$names = explode('\\', $classname);
	foreach ($names as &$name) {
		$new_name = strtolower($name[0]);
		for ($i = 1; $i < strlen($name); ++$i) {
			if (ctype_upper($name[$i]))
				$new_name .= '_';
			$new_name .= strtolower($name[$i]);
		}
		$name = $new_name;
	}
	require_once implode('/', $names) . '.php';
}

?>