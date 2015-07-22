<?php
namespace DemoShop;

define('DEMOSHOP_PATH', realpath('..'));
set_error_handler(function ($code, $string) { throw new \Exception($string, $code); }, E_ALL | E_STRICT);
require DEMOSHOP_PATH . '/config.php';
require DEMOSHOP_PATH . '/classes/autoload.php';

try {
	ob_start();

	/**
	 * Подключение к базе данных
	 */
	Database::setConnString(DEMOSHOP_DATABASE);

	/**
	 * Инициализация приложения
	 */
	$app = new App();


	/**
	 * http://demoshop.actionpay.ru/shop.yml
	 * Генерация YML-каталога
	 */
	$app->page('/shop.yml', function () use ($app) {
		$xml = '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL
			 . '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">' . PHP_EOL
			 . '<yml_catalog date="' . date('Y-m-d H:i:s') . '">' . PHP_EOL
			 . '	<shop>' . PHP_EOL
			 . '		<name>Овощи&amp;Фрукты</name>' . PHP_EOL
			 . '		<company>ООО "Овощи&amp;Фрукты"</company>' . PHP_EOL
			 . '		<url>http://' . DEMOSHOP_DOMAIN . '</url>' . PHP_EOL
			 . '		<agency>Actionpay</agency>' . PHP_EOL
			 . '		<currencies>' . PHP_EOL
			 . '			<currency id="RUR" rate="1" />' . PHP_EOL
			 . '		</currencies>' . PHP_EOL
			 . '		<categories>' . PHP_EOL;

		$categories = Category::getAll();
		foreach ($categories as $category) {
			$xml .= '			<category id="' . $category->id . '"'
								. ($category->parent_id ? ' parentId="' . $category->parent_id . '"' : '')
								. '>' . $category->name . '</category>' . PHP_EOL;
		}
		$xml .= '		</categories>' . PHP_EOL;

		$xml .= '		<offers>' . PHP_EOL;
		$products = Product::getAll();
		foreach ($products as $product) {
			$xml .= '			<offer id="' . $product->id . '" available="true">' . PHP_EOL;
			$xml .= '				<url>http://' . DEMOSHOP_DOMAIN . '/product?p=' . $product->id . '</url>' . PHP_EOL;
			$xml .= '				<price>' . $product->price . '</price>' . PHP_EOL;
			$xml .= '				<currencyId>RUR</currencyId>' . PHP_EOL;
			$xml .= '				<categoryId>' . $product->category_id . '</categoryId>' . PHP_EOL;
			$xml .= '				<picture>http://' . DEMOSHOP_DOMAIN . '/img/big/' . $product->image . '</picture>' . PHP_EOL;
			$xml .= '				<name>' . $product->name . '</name>' . PHP_EOL;
			$xml .= '			</offer>' . PHP_EOL;
		}
		$xml .= '		</offers>' . PHP_EOL
			  . '	</shop>' . PHP_EOL
			  . '</yml_catalog>' . PHP_EOL;

		header('Content-Type: text/xml');
		return $xml;
	});


	/**
	 * http://demoshop.actionpay.ru/fruits
	 * Специальный лендинг для категории "Фрукты"
	 */
	$app->page('/fruits', function () use ($app) {
		return $app->render('page_landing', array(
			'title' => 'Фрукты',
			'image' => '/img/landing/fruits.jpg',
			'promo' => 'Не психуй, купи вкусных сочных маракуй!',
			'link' => '/?c=' . Category::FRUITS_ID
		));
	});


	/**
	 * http://demoshop.actionpay.ru/vegetables
	 * Специальный лендинг для категории "Овощи"
	 */
	$app->page('/vegetables', function () use ($app) {
		return $app->render('page_landing', array(
			'title' => 'Фрукты',
			'image' => '/img/landing/vegetables.jpg',
			'promo' => 'Купи овощей, свари восхитительных щей!',
			'link' => '/?c=' . Category::VEGETABLES_ID
		));
	});


	/**
	 * http://demoshop.actionpay.ru/buy?product=<PRODUCT_ID>&count=<COUNT>[&ajax=true]
	 * Метод для изменения количества товара №<PRODUCT_ID> на <COUNT> единиц
	 * Количество единиц товара может быть отрицательным, что означает удаление из корзины
	 * ajax=true указывает на необходимость вернуть JSON с данными, иначе будет сделан редирект
	 */
	$app->page('/buy', function (Product $product, $count = 1, $ajax = false) use ($app) {
		$basket = $app->session('basket') ?: array();
		$count = (int)$count;
		$basket[$product->id] = isset($basket[$product->id]) ? $basket[$product->id] + $count : $count;
		if ($basket[$product->id] <= 0) {
			unset($basket[$product->id]);
		}
		$basketTotalItems = 0;
		$basketTotalPrice = 0;
		if (is_array($basket)) {
			foreach ($basket as $basketProductId => $basketProductCount) {
				$basketTotalItems += $basketProductCount;
				$basketTotalPrice += Product::getById($basketProductId)->price * $basketProductCount;
			}
		}

		$app->session('basket', $basket);
		$app->session('basketTotalItems', $basketTotalItems);
		$app->session('basketTotalPrice', $basketTotalPrice);

		if ($ajax) {
			return json_encode(array(
				'ok' => true,
				'productId' => $product->id,
				'productName' => $product->name,
				'productNewCount' => isset($basket[$product->id]) ? $basket[$product->id] : 0,
				'basketTotalItems' => $basketTotalItems,
				'basketTotalPrice' => $basketTotalPrice,
				'basketTotalPriceFormatted' => Product::formatPrice($basketTotalPrice)
			));
		} else {
			$app->redirect($_SERVER['HTTP_REFERER'] ?: '/');
			return '';
		}
	});

	/**
	 * http://demoshop.actionpay.ru/basket
	 * Страница корзины
	 */
	$app->page('/basket', function () use ($app) {
		$basket = $app->session('basket');

		if (!empty($basket)) {
			$products = Product::getAll(array('id' => array_keys($basket)), array('name' => true));
		} else {
			$products = array();
		}

		return $app->render('page_basket', array(
			'title' => 'Корзина',
			'products' => $products,
			'basket' => $basket,
			'basketTotalItems' => $app->session('basketTotalItems') ?: 0,
			'basketTotalPrice' => $app->session('basketTotalPrice') ?: 0,
		));
	});


	/**
	 * http://demoshop.actionpay.ru/order
	 * Страница оформления заказа
	 */
	$app->page('/order', function ($name = null, $address = null, $phone = null) use ($app) {
		$basket = $app->session('basket');

		// если в корзине пусто, отправляем в каталог товаров
		if (empty($basket)) {
			$app->redirect('/');
			return '';
		}

		// если поля формы переданы и заполнены, сохраняем заказ
		if ($name && $address && $phone) {
			$order = new Order();
			$order->date = date('Y-m-d H:i:s');
			$order->status = Order::STATUS_UNCONFIRMED;
			$order->client_name = $name;
			$order->client_phone = $phone;
			$order->client_address = $address;
			$order->save();

			foreach ($basket as $productId => $count) {
				$orderProduct = new OrderProduct();
				$orderProduct->order_id = $order->id;
				$orderProduct->product_id = $productId;
				$orderProduct->count = $count;
				$orderProduct->save();
			}

			// редирект на страницу "спасибо"
			$app->redirect('/thankyou?order=' . $order->id);

			// очистка корзины
			$app->session('basket', array());
			$app->session('basketTotalItems', 0);
			$app->session('basketTotalPrice', 0);

			return '';
		}

		// отображение формы заказа
		return $app->render('page_order', array(
			'title' => 'Оформление заказа',
		));
	});


	/**
	 * http://demoshop.actionpay.ru/thankyou
	 * Страница "спасибо за заказ"
	 */
	$app->page('/thankyou', function (Order $order) use ($app) {
		return $app->render('page_thankyou', array(
			'title' => 'Спасибо за заказ',
			'order' => $order
		));
	});


	/**
	 * http://demoshop.actionpay.ru/admin/order?order=<ORDER_ID>
	 * Страница просмотра заказа для сотрудника магазина
	 */
	$app->page('/admin/order', function (Order $order, $status) use ($app) {
		$status = (int)$status;

		// если запрошено изменение статуса заказа
		if (array_key_exists($status, Order::$statusList)) {
			$order->status = $status;
			$order->save();
			$app->redirect('/admin/order?order=' . $order->id);
			return '';
		}

		return $app->render('page_admin_order', array(
			'title' => 'Заказ',
			'order' => $order
		));
	});


	/**
	 * http://demoshop.actionpay.ru/admin
	 * Страница просмотра всех заказов для сотрудника магазина
	 */
	$app->page('/admin', function () use ($app) {
		$orders = Order::getAll(array(), array('id' => false));

		return $app->render('page_admin', array(
			'title' => 'Заказы',
			'orders' => $orders
		));
	});


	/**
	 * http://demoshop.actionpay.ru/product?p=<PRODUCT_ID>
	 * Страница просмотра одного товара
	 */
	$app->page('/product', function (Product $p) use ($app) {
		return $app->render('page_product', array(
			'title' => $p->name,
			'product' => $p,
			'basket' => $app->session('basket') ?: array(),
			'basketTotalItems' => $app->session('basketTotalItems') ?: 0,
			'basketTotalPrice' => $app->session('basketTotalPrice') ?: 0,
		));
	});


	/**
	 * http://demoshop.actionpay.ru/[?c=<CATEGORY_ID>]
	 * Главная страница / главный лендинг / просмотр всех товаров
	 * При наличии <CATEGORY_ID> - просмотр товаров в этой категории
	 */
	$app->page('/', function (Category $c = null) use ($app) {
		/** @var Category[] $parentCategories */
		$category = $c;

		// помимо текущей выбранной категории, нужно отображать товары из ее дочерних категорий
		$shownCategoryIds = array();
		if ($category) {
			$shownCategoryIds []= $category->id;
			foreach ($category->getAllChildren() as $childCategory) {
				$shownCategoryIds []= $childCategory->id;
			}
		}

		// определяем параметры выборки товаров
		$productCriteria = array();
		if (count($shownCategoryIds) > 0) {
			$productCriteria['category_id'] = $shownCategoryIds;
		}

		$products = Product::getAll($productCriteria, array('name' => true));

		return $app->render('page_main', array(
			'title' => $category ? $category->name : 'Главная',
			'category' => $category,
			'products' => $products,
			'basket' => $app->session('basket') ?: array(),
			'basketTotalItems' => $app->session('basketTotalItems') ?: 0,
			'basketTotalPrice' => $app->session('basketTotalPrice') ?: 0,
		));
	});


	/**
	 * Запуск приложения, вывод ответа
	 */
	echo $app->run();
	ob_end_flush();

} catch (\Exception $e) {
	/**
	 * Ловушка для всех непойманных исключений и ошибок
	 */

	ob_end_clean();
	echo 'Internal server error';

	if (DEMOSHOP_DEBUG) {
		echo '<pre>';
		do {
			echo get_class($e) . ' (code ' . $e->getCode() . '): ';
			echo $e->getMessage() . PHP_EOL;
			echo $e->getTraceAsString();
		} while ($e = $e->getPrevious());
		echo '</pre>';
	}
}
