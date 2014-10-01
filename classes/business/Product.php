<?php
namespace DemoShop;

class Product extends AbstractDbObject {
	const TABLE_NAME = 'product';

	/** @dbcolumn */
	public $name;
	/** @dbcolumn */
	public $image;
	/** @dbcolumn */
	public $price;
	/** @dbcolumn */
	public $category_id;

	protected $category;

	/**
	 * @return Category|null
	 */
	public function getCategory() {
		if (!$this->category && $this->category_id) {
			$this->category = Category::getById($this->category_id);
		}
		return $this->category;
	}

	public static function formatPrice($price) {
		return sprintf(
			'<b>%s</b><sup>%02d</sup> руб.',
			number_format(floor($price), 0, ',', ' '),
			round(($price - floor($price)) * 100)
		);
	}

	public function getPriceFormatted() {
		return self::formatPrice($this->price);
	}
} 