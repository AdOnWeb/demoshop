<?php
namespace DemoShop;

class Database {
	protected static $instance = null;
	protected static $connString = null;

	protected $link = null;

	public static function get() {
		return self::$instance = self::$instance ?: new Database();
	}

	public static function setConnString($connString) {
		self::$connString = $connString;
	}

	public function checkConnection() {
		if (!$this->link) {
			$link = pg_connect(self::$connString);
			if (!$link) {
				throw new \Exception(pg_last_error());
			}
			$this->link = $link;
		}
	}

	public function select($table, $where = array(), $order = array(), $single = false) {
		$this->checkConnection();

		$query = 'SELECT * FROM ' . $this->field($table);
		$conditions = array();
		foreach ($where as $whereField => $whereValue) {
			if ($whereValue === null) {
				$conditions []= $this->field($whereField) . ' IS NULL';
			} else if (is_array($whereValue)) {
				$whereValueArray = array();
				foreach ($whereValue as $whereValueArrayItem) {
					$whereValueArray []= $this->value($whereValueArrayItem);
				}
				$conditions []= $this->field($whereField) . ' IN (' . implode(', ', $whereValueArray) . ')';
			} else {
				$operators = array('>', '<', '>=', '<=', '!=', '=');
				foreach ($operators as $operator) {
					if (substr($whereField, -strlen($operator)) == $operator) {
						$whereField = substr($whereField, 0, strlen($whereField) - strlen($operator));
						break;
					}
				}
				$conditions []= $this->field($whereField) . ' ' . $operator . ' ' . $this->value($whereValue);
			}
		}
		if (count($conditions) > 0) {
			$query .= ' WHERE ' . implode(' AND ', $conditions);
		}

		$orderings = array();
		foreach ($order as $orderField => $orderDirection) {
			$orderings []= $this->field($orderField) . ' ' . ($orderDirection ? 'ASC' : 'DESC');
		}
		if (count($orderings) > 0) {
			$query .= ' ORDER BY ' . implode(', ', $orderings);
		}
		if ($single) {
			$query .= ' LIMIT 1';
		}
		$res = $this->query($query);
		return $single ? pg_fetch_assoc($res) : (pg_fetch_all($res) ?: array() );
	}

	public function update($table, $data = array(), $where = array()) {
		$this->checkConnection();

		$isInserting = (empty($where) == true);
		if ($isInserting && array_key_exists('id', $data)) {
			$data['id'] = $this->getSequenceNextVal($table . '_id');
		}
		$query = ($isInserting ? 'INSERT INTO' : 'UPDATE') . ' "' . pg_escape_string($this->link, $table) . '"';
		$assignments = array();
		$fields = array();
		$values = array();
		foreach ($data as $field => $value) {
			$field = $this->field($field);
			$value = $this->value($value);
			$assignments []= $field . ' = ' . $value;
			$fields []= $field;
			$values []= $value;
		}
		if (empty($where)) {
			$query .= ' (' . implode(', ', $fields). ') VALUES (' . implode(', ', $values) . ')';
		} else {
			$query .= ' SET ' . implode(', ', $assignments);
		}

		$conditions = array();
		foreach ($where as $whereField => $whereValue) {
			$conditions []= '"' . pg_escape_string($whereField) . '" = \'' . pg_escape_string($whereValue) . '\'';
		}
		if (count($conditions) > 0) {
			$query .= ' WHERE ' . implode(' AND ', $conditions);
		}
		$query .= ' RETURNING ' . implode(', ', $fields);

		$res = $this->query($query);
		return pg_fetch_assoc($res);
	}

	public function getSequenceNextVal($sequenceName) {
		$this->checkConnection();
		$res = $this->query('SELECT NEXTVAL(' . $this->value($sequenceName) . ')');
		return pg_fetch_result($res, null, 'nextval');
	}

	protected function query($query) {
		$res = pg_query($this->link, $query);
		if (!$res) {
			throw new \Exception(pg_last_error($this->link));
		}
		return $res;
	}

	protected function field($field) {
		return '"' . pg_escape_string($field) . '"';
	}

	protected function value($value) {
		if ($value === null) {
			return 'NULL';
		}
		if ($value === true) {
			return 'TRUE';
		}
		if ($value === false) {
			return 'FALSE';
		}

		return '\'' . pg_escape_string($value) . '\'';
	}

}