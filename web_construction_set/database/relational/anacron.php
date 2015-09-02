<?php

namespace WebConstructionSet\Database\Relational;

/**
 * БД Anacron
 * Периодическое выполнение задач
 */
class Anacron {
	private $db, $table;
	const MAX_DATA_SIZE = 1024;

	public function __construct(\WebConstructionSet\Database\Relational $db, $table = 'anacron') {
		$this->db = $db;
		$this->table = $table;
	}

	/**
	 * Создать задачу
	 * @param array $task [start => Unix timestamp, pariod => seconds, data => mixed]
	 * @param integer $taskKey
	 * @return integer taskId
	 */
	public function create($task, $taskKey = 0) {
		$data = json_encode($task['data']);
		if (strlen($data) > Anacron::MAX_DATA_SIZE)
			return 0;
		return $this->db->insert($this->table, ['start_time' => $task['start'], 'period_time' => $task['period'], 'data' => $data, 'user_key' => $taskKey]);
	}

	/**
	 * Получить список задач, готовых к выполнению
	 * @param integer $taskKey null - all tasks
	 * @return array [id => integer, data => mixed]
	 */
	public function ready($taskKey = 0) {
		$filter = ['start_time' => new \WebConstructionSet\Database\Relational\Pdo\Predicate\LessEq(time())];
		if ($taskKey !== null)
			$filter['user_key'] = $taskKey;
		$data = $this->db->select($this->table, ['id', 'start_time', 'period_time', 'data', 'user_key'], $filter);
		$tasks = [];
		foreach ($data as $task) {
			$time = $task['start_time'];
			while ($time < time())
				$time += $task['period_time'];
			if ($this->db->update($this->table, ['start_time' => $time], ['id' => $task['id'], 'start_time' => $task['start_time']]))
				$tasks[] = ['id' => $task['id'], 'data' => json_decode($task['data'], true /* assoc */), 'key' => $task['user_key']];
		}
		return $tasks;
	}

	/**
	 * Получить список зарегистрированных задач
	 * @param array $taskIds
	 * @param integer $taskKey null - all tasks
	 * @return array [id => integer, start => integer, period => integer, data => mixed]
	 */
	public function get($taskIds = null, $taskKey = 0) {
		$filter = [];
		if ($taskKey !== null)
			$filter['user_key'] = $taskKey;
		$data = [];
		if ($taskIds)
			foreach ($taskIds as $taskId) {
				$task = $this->db->select($this->table, ['id', 'start_time', 'period_time', 'data', 'user_key'], array_merge($filter, ['id' => $taskId]));
				if ($task)
					$data = array_merge($data, $task);
			}
		else
			$data = $this->db->select($this->table, ['id', 'start_time', 'period_time', 'data', 'user_key'], $filter);
		$tasks = [];
		foreach ($data as $task)
			$tasks[] = ['id' => $task['id'], 'start' => $task['start_time'], 'period' => $task['period_time'],
				'data' => json_decode($task['data'], true /* assoc */), 'key' => $task['user_key']];
		return $tasks;
	}

	/**
	 * Обновить задачу
	 * @param integer $taskId
	 * @param array $task [start (optional) => Unix timestamp, pariod (optional) => seconds, data (optional) => mixed]
	 * @param integer $taskKey
	 * @return boolean
	 */
	public function update($taskId, $task, $taskKey = 0) {
		$data = [];
		foreach (['start' => 'start_time', 'period' => 'period_time', 'data' => 'data'] as $param => $field)
			if (isset($task[$param]))
				$data[$field] = $task[$param];
		if (isset($data['data'])) {
			$data['data'] = json_encode($data['data']);
			if (strlen($data['data']) > Anacron::MAX_DATA_SIZE)
				return 0;
		}
		return $this->db->update($this->table, $data, ['id' => $taskId, 'user_key' => $taskKey]);
	}

	/**
	 * Удалить задачу
	 * @param integer $taskId
	 * @param integer $taskKey
	 * @return boolean
	 */
	public function delete($taskId, $taskKey = 0) {
		return $this->db->delete($this->table, ['id' => $taskId, 'user_key' => $taskKey]);
	}
}
