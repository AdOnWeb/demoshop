<?php
namespace DemoShop;

class Order extends AbstractDbObject {
	const TABLE_NAME = 'order';

	const STATUS_UNCONFIRMED = 1;
	const STATUS_CONFIRMED = 2;
	const STATUS_DELIVERED = 3;
	const STATUS_CANCELED = 9;

	const ORDERED_ON_WEBSITE = 1;
	const ORDERED_BY_PHONE = 2;

	public static $statusList = array(
		self::STATUS_UNCONFIRMED 	=> 'Не подтвержден',
		self::STATUS_CONFIRMED 		=> 'Подтвержден',
		self::STATUS_DELIVERED 		=> 'Доставлен и оплачен',
		self::STATUS_CANCELED 		=> 'Отменен',
	);

	public static $orderedOnList = array(
        self::ORDERED_ON_WEBSITE => 'Заказ с сайта',
        self::ORDERED_BY_PHONE   => 'Заказ по телефону'
    );

	/** @dbcolumn */
	public $date;
	/** @dbcolumn */
	public $status;
	/** @dbcolumn */
	public $client_name;
	/** @dbcolumn */
	public $client_phone;
	/** @dbcolumn */
	public $client_address;
	/** @dbcolumn */
	public $ordered_on;
	/** @dbcolumn */
	public $partner_name;
	/** @dbcolumn */
	public $partner_traffic_id;
	/** @dbcolumn */
	public $partner_order_id;

	protected $products = null;

    /**
     * @return string
     */
	public function getStatusName() {
		if (isset(self::$statusList[$this->status])) {
			return self::$statusList[$this->status];
		}
		return 'Неизвестно';
	}

    /**
     * @return string
     */
    public function getOrderedOnName() {
        if (isset(self::$orderedOnList[$this->ordered_on])) {
            return self::$orderedOnList[$this->ordered_on];
        }
        return '';
	}

	/**
	 * @return OrderProduct[]
	 */
	public function getOrderedProducts() {
		if ($this->products === null) {
			$this->products = OrderProduct::getAll(array('order_id' => $this->id));
		}
		return $this->products;
	}

	public function getTotalPrice() {
		$totalPrice = 0;
		foreach ($this->getOrderedProducts() as $orderProduct) {
			$totalPrice += $orderProduct->getProduct()->price * $orderProduct->count;
		}
		return $totalPrice;
	}

}