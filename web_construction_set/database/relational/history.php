<?php

namespace WebConstructionSet\Database\Relational;

/**
 * БД Лога
 * История операций
 * Хранилище событий
 */
class History {
	private $db, $table;
	const MAX_DATA_SIZE = 1024;

	public function __construct(\WebConstructionSet\Database\Relational $db, $table = 'history') {
		$this->db = $db;
		$this->table = $table;
	}

	/**
	 * Добавить событие
	 * @param string $name
	 * @param mixed $data
	 * @param integer $key
	 * @return boolean
	 */
	public function add($name, $data, $key = 0) {
		$data = json_encode($data);
		if (strlen($data) > History::MAX_DATA_SIZE)
			return false;
		return $this->db->insert($this->table, ['name' => $name, 'data' => $data, 'time' => time(), 'user_key' => $key]);
	}

	/**
	 * Получить набор событий
	 * @param integer|null $key null - все события
	 * @return [][name => string, time => integer, data => mixed, key => integer]
	 */
	public function get($key = 0) {
		$filter = [];
		if ($key !== null)
			$filter['user_key'] = $key;
		$data = $this->db->select($this->table, ['name', 'data', 'time', 'user_key'], $filter);
		$history = [];
		foreach ($data as $event)
			$history[] = ['name' => $event['name'], 'time' => $event['time'],
				'data' => json_decode($event['data'], true), 'key' => $event['user_key']];
		return $history;
	}

	/**
	 * Уладить события
	 * @param integer $time более старые события будут удалены
	 * @return integer Количество удаленных
	 */
	public function clear($time) {
		return $this->db->delete($this->table, ['time' => $this->db->predicate('less', $time)]);
	}
}
