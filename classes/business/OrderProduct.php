<?php
namespace DemoShop;

class OrderProduct extends AbstractDbObject {
	const TABLE_NAME = 'order_product';

	/** @dbcolumn */
	public $order_id;
	/** @dbcolumn */
	public $product_id;
	/** @dbcolumn */
	public $count;

	protected $order = null;
	protected $product = null;

	/**
	 * @return Product
	 */
	public function getProduct() {
		if ($this->product_id && !$this->product) {
			$this->product = Product::getById($this->product_id);
		}
		return $this->product;
	}

	/**
	 * @return Order
	 */
	public function getOrder() {
		if ($this->order_id && !$this->order) {
			$this->order = Product::getById($this->order_id);
		}
		return $this->product;
	}

}