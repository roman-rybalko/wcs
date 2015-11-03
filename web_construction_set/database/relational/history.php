<?php

namespace WebConstructionSet\Database\Relational;

/**
 * БД Лога
 * История операций
 * Хранилище событий
 */
class History {
	private $table;
	const MAX_DATA_SIZE = 1024;

	public function __construct(\WebConstructionSet\Database\Relational $db, $key = null, $tableName = 'history') {
		$fields = [];
		if ($key !== null)
			$fields['user_key'] = $key;
		$this->table = new \WebConstructionSet\Database\Relational\TableWrapper($db, $tableName, $fields);
	}

	/**
	 * Добавить событие
	 * @param string $name
	 * @param mixed $data
	 * @return boolean
	 */
	public function add($name, $data) {
		$data = json_encode($data);
		if (strlen($data) > History::MAX_DATA_SIZE)
			return false;
		return $this->table->insert(['name' => $name, 'data' => $data, 'time' => time()]);
	}

	/**
	 * Получить набор событий
	 * @return [][name => string, time => integer, data => mixed, key => integer]
	 */
	public function get() {
		$data = $this->table->select(['name', 'data', 'time', 'user_key']);
		$history = [];
		foreach ($data as $event)
			$history[] = ['name' => $event['name'], 'time' => $event['time'],
				'data' => json_decode($event['data'], true /* assoc */), 'key' => $event['user_key']];
		return $history;
	}

	/**
	 * Уладить события
	 * @param integer $time более старые события будут удалены
	 * @return integer Количество удаленных
	 */
	public function clear($time) {
		return $this->table->delete(['time' => $this->table->predicate('less', $time)]);
	}
}
