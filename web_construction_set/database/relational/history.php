<?php

namespace WebConstructionSet\Database\Relational;

/**
 * БД Лога
 * История операций
 * Хранилище событий
 */
class History {
	private $table;
	const MAX_DATA_SIZE = 16384;

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
			throw new \ErrorException('User Data too large', null, null, __FILE__, __LINE__);
		return $this->table->insert(['name' => $name, 'data' => $data, 'time' => time()]);
	}

	/**
	 * Получить набор событий
	 * @param integer $time Unix Time, с какого времени вернуть данные, по-умолчанию 0 т.е. все данные
	 * @return [][name => string, time => integer, data => mixed, key => integer]
	 */
	public function get($time = 0) {
		$data = $this->table->select(['name', 'data', 'time', 'user_key'], ['time' => $this->table->predicate('ge', $time)]);
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
