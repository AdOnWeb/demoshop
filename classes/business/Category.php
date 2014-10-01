<?php
namespace DemoShop;

class Category extends AbstractDbObject {
	const TABLE_NAME = 'category';

	const FRUITS_ID = 1;
	const VEGETABLES_ID = 2;

	/** @dbcolumn */
	public $name;
	/** @dbcolumn */
	public $parent_id;

	protected $parent;
	protected $children;

	/**
	 * @return self|null
	 */
	public function getParent(){
		if (!$this->parent && $this->parent_id) {
			$this->parent = Category::getById($this->parent_id);
		}
		return $this->parent;
	}

	/**
	 * @return self[]
	 */
	public function getAllParents() {
		$parent = $this->getParent();
		$allParents = array();
		while ($parent) {
			$allParents []= $parent;
			$parent = $parent->getParent();
		}
		return $allParents;
	}

	/**
	 * @return self[]
	 */
	public function getChildren() {
		if ($this->children === null) {
			$this->children = self::getAll(['parent_id' => $this->id]);
		}
		return $this->children;
	}

	/**
	 * @return self[]
	 */
	public function getAllChildren() {
		$allChildren = $this->getChildren();
		foreach ($allChildren as $child) {
			$allChildren = array_merge($allChildren, $child->getAllChildren());
		}
		return $allChildren;
	}
}