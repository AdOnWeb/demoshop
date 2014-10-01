<?php
namespace DemoShop;

abstract class AbstractDbObject {
	const TABLE_NAME = '';

	/** @dbcolumn */
	public $id;

	public static function getMapping() {
		$rfClass = new \ReflectionClass(get_called_class());
		$mappedProperties = array();
		foreach ($rfClass->getProperties() as $rfProperty) {
			if ($rfProperty->getDocComment() && strpos($rfProperty->getDocComment(), '@dbcolumn') !== false) {
				$mappedProperties []= $rfProperty->getName();
			}
		}
		return $mappedProperties;
	}

	/**
	 * @param $id
	 * @return static|null
	 */
	public static function getById($id) {
		$row = Database::get()->select(static::TABLE_NAME, array('id' => (int)$id), array(), true);
		if ($row) {
			$object = new static;
			return $object->load($row);
		} else {
			return null;
		}
	}

	/**
	 * @param array $where
	 * @param array $order
	 * @return static[]
	 */
	public static function getAll($where = array(), $order = array('id' => true)) {
		$mappedWhere = array();
		$mapping = static::getMapping();
		foreach ($where as $property => $value) {
			if (isset($mapping[$property])) {
				$mappedWhere[$mapping[$property]] = $value;
			} else {
				$mappedWhere[$property] = $value;
			}
		}
		$rows = Database::get()->select(static::TABLE_NAME, $mappedWhere, $order);
		$objects = array();
		foreach ($rows as $row) {
			$object = new static;
			$objects []= $object->load($row);
		}
		return $objects;
	}

	protected function load($row) {
		$mapping = static::getMapping();
		foreach ($row as $field => $value) {
			if (in_array($field, $mapping)) {
				$this->$field = $value;
			}
		}
		return $this;
	}

	public function save() {
		$fields = array();
		$mapping = static::getMapping();
		foreach ($mapping as $field) {
			$fields[$field] = $this->$field;
		}
		if ($this->id !== null) {
			$where = array('id' => $this->id);
		} else {
			$where = array();
		}
		$row = Database::get()->update(static::TABLE_NAME, $fields, $where);
		return $this->load($row);
	}

} 