<?php

namespace WebConstructionSet\Database\Relational;

/**
 * БД Anacron
 * Периодическое выполнение задач
 */
class Anacron {
	private $table;
	const MAX_DATA_SIZE = 1024;

	public function __construct(\WebConstructionSet\Database\Relational $db, $key = null, $tableName = 'anacron') {
		$fields = [];
		if ($key !== null)
			$fields['user_key'] = $key;
		$this->table = new \WebConstructionSet\Database\Relational\TableWrapper($db, $tableName, $fields);
	}

	/**
	 * Создать задачу
	 * @param integer $start Unix timestamp
	 * @param integer $period seconds
	 * @param mixed $data
	 * @return integer id
	 */
	public function create($start, $period, $data) {
		$data = json_encode($data);
		if (strlen($data) > Anacron::MAX_DATA_SIZE)
			return 0;
		return $this->table->insert(['start_time' => $start, 'period_time' => $period, 'data' => $data]);
	}

	/**
	 * Получить список задач, готовых к выполнению
	 * @return [integer]
	 */
	public function ready() {
		$filter = ['start_time' => $this->table->predicate('less_eq', time())];
		$data = $this->table->select(['id', 'start_time', 'period_time'], ['start_time' => $this->table->predicate('less_eq', time())]);
		$ids = [];
		foreach ($data as $data1) {
			$time = $data1['start_time'];
			while ($time <= time())
				$time += $data1['period_time'];
			if ($this->table->update(['start_time' => $time], ['id' => $data1['id'], 'start_time' => $data1['start_time']]))
				$ids[] = $data1['id'];
		}
		return $ids;
	}

	/**
	 * Получить список зарегистрированных задач
	 * @param [integer] $ids
	 * @return [][id => integer, start => integer, period => integer, data => mixed, key => integer]
	 */
	public function get($ids = null) {
		$fields = ['id', 'start_time', 'period_time', 'data', 'user_key'];
		$data = [];
		if ($ids !== null)
			foreach ($ids as $taskId) {
				$data1 = $this->table->select($fields, ['id' => $taskId]);
				if ($data1)
					$data = array_merge($data, $data1);
			}
		else
			$data = $this->table->select($fields);
		$tasks = [];
		foreach ($data as $data1)
			$tasks[] = ['id' => $data1['id'], 'start' => $data1['start_time'], 'period' => $data1['period_time'],
				'data' => json_decode($data1['data'], true /* assoc */), 'key' => $data1['user_key']];
		return $tasks;
	}

	/**
	 * Обновить задачу
	 * @param integer $id
	 * @param integer|null $start Unix timestamp
	 * @param integer|null $period seconds
	 * @param mixed|null $fields
	 * @return boolean
	 */
	public function update($id, $start = null, $period = null, $data = null) {
		$fields = [];
		if ($start !== null)
			$fields['start_time'] = $start;
		if ($period !== null)
			$fields['period_time'] = $period;
		if ($data !== null) {
			$fields['data'] = json_encode($data);
			if (strlen($fields['data']) > Anacron::MAX_DATA_SIZE)
				return false;
		}
		if (!$fields)
			return false;
		return $this->table->update($fields, ['id' => $id]);
	}

	/**
	 * Удалить задачу
	 * @param integer $id
	 * @return boolean
	 */
	public function delete($id) {
		return $this->table->delete(['id' => $id]);
	}
}
