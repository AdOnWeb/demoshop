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
	 * Проверяем параметры запроса на наличие параметров перехода из CPA-сетей
	 * CPA::checkTraffic() вернет true, если пользователь пришел на сайт по партнерской ссылке
	 */
	$isNewCpaTraffic = \Actionpay\CPA::checkTraffic();

	// в целях демонстрации, идентификационные параметры партнера будут выведены на странице
	if ($isNewCpaTraffic) {
		$app->addInfoPopup(
			'Вы перешли из <b>' . \Actionpay\CPA::getLastPartnerName() . '</b><br />' .
			'Идентификатор перехода: <b>' . \Actionpay\CPA::getLastTrafficIdentifer() . '</b>'
		);
	}

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
	 * http://demoshop.actionpay.ru/actionpay.xml
	 * XML-отчет по заказам для Actionpay
	 */
	$app->page('/actionpay.xml', function () use ($app) {
		header('Content-Type: text/xml');
		$xml = '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL;

		// параметры выборки заказов
		$ordersCriteria = array('partner_name' => 'actionpay');

		$password = md5('123456');
		if (!isset($_POST['pass']) || $_POST['pass'] != $password) {
			return $xml . '<error>wrong password</error>' . PHP_EOL;

		} else if (isset($_POST['xml'])) {
			// запрос по id заказов
			preg_match_all('#<item>([^<]+)</item>#', $_POST['xml'], $items);
			$orderIds = isset($items[1]) ? $items[1] : array();

			if (count($orderIds) > 0) {
				$ordersCriteria['partner_order_id'] = $orderIds;
			}

		} else if (isset($_POST['date'])) {
			// запрос за временной период
			$startDate = strtotime($_POST['date']);

			if (!$startDate) {
				return $xml . '<error>wrong request: bad "date" param</error>' . PHP_EOL;

			} else {
				$ordersCriteria['date>='] = date('Y-m-d H:i:s', $startDate);
			}

		} else {
			return $xml . '<error>wrong request: no "xml" or "date" param</error>' . PHP_EOL;
		}

		// получаем заказы из БД
		$orders = Order::getAll($ordersCriteria);

		// генерация списка заказов в XML
		if (count($orders) > 0) {
			$xml .= '<items>' . PHP_EOL;
			foreach ($orders as $order) {
				switch ($order->status) {
					// заказ оплачен и доставлен -> действие необходимо принять
					case Order::STATUS_DELIVERED: 	$status = \Actionpay\CPA::ACTIONPAY_STATUS_ACCEPT; 	break;
					// заказ отменён -> действие необходимо отклонить
					case Order::STATUS_CANCELED:  	$status = \Actionpay\CPA::ACTIONPAY_STATUS_REJECT; 	break;
					// все другие неокончательные статусы заказа -> действие находится в обработке
					default:					  	$status = \Actionpay\CPA::ACTIONPAY_STATUS_PROCESSING;
				}
				// определяем клик и источник из идентификатора трафика
				$source = null;
				$click = null;
				if (strpos($order->partner_traffic_id, '.') !== false) {
					// есть [клик].[источник]
					list($click, $source) = explode('.', $order->partner_traffic_id);
				} else if (is_numeric($order->partner_traffic_id)) {
					// есть только источник
					$source = $order->partner_traffic_id;
				} else {
					// есть только клик
					$click = $order->partner_traffic_id;
				}

				$xml .= '	<item>' . PHP_EOL;
				$xml .= '		<id>' . 	$order->partner_order_id . '</id>' . PHP_EOL;
				$xml .= '		<date>' . 	$order->date . '</date>' . PHP_EOL;
				$xml .= '		<status>' . $status . '</status>' . PHP_EOL;
				$xml .= '		<price>' . 	$order->getTotalPrice() . '</price>' . PHP_EOL;
				$xml .= '		<source>' . $source . '</source>' . PHP_EOL;
				$xml .= '		<click>' . 	$click . '</click>' . PHP_EOL;
				$xml .= '	</item>' . PHP_EOL;
			}
			$xml .= '</items>' . PHP_EOL;
		}

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
			'link' => '/?c=' . Category::FRUITS_ID,
			'aprtData' => array(
				'pageType' => \Actionpay\APRT::PAGETYPE_MAIN
			)
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
			'link' => '/?c=' . Category::VEGETABLES_ID,
			'aprtData' => array(
				'pageType' => \Actionpay\APRT::PAGETYPE_MAIN
			)
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
				'basketTotalPriceFormatted' => Product::formatPrice($basketTotalPrice),
				'aprtData' => array(
					'pageType' => $count > 0 ? \Actionpay\APRT::PAGETYPE_CART_ADD : \Actionpay\APRT::PAGETYPE_CART_REMOVE,
					'currentProduct' => array(
						'id' 	=> $product->id,
						'name' 	=> $product->name,
						'price' => $product->price
					)
				)
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

		$aprtData = array(
			'pageType' => \Actionpay\APRT::PAGETYPE_BASKET,
			'basketProducts' => array()
		);
		foreach ($products as $product) {
			$aprtData['basketProducts'][] = array(
				'id' 		=> $product->id,
				'name' 		=> $product->name,
				'price' 	=> $product->price,
				'quantity' 	=> $basket[$product->id]
			);
		}

		return $app->render('page_basket', array(
			'title' => 'Корзина',
			'products' => $products,
			'basket' => $basket,
			'basketTotalItems' => $app->session('basketTotalItems') ?: 0,
			'basketTotalPrice' => $app->session('basketTotalPrice') ?: 0,
			'aprtData' => $aprtData
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

			/**
			 * Если клиент был приведён на сайт через партнёра, сохраним в заказ партнерскую информацию
			 */
			if (\Actionpay\CPA::getLastPartnerName()) {
				// Имя партнёра и идентификатор трафика, хранящиеся в cookie клиента
				$order->partner_name 		= \Actionpay\CPA::getLastPartnerName();
				$order->partner_traffic_id 	= \Actionpay\CPA::getLastTrafficIdentifer();
				// Генерируем уникальный ID для отслеживания заказа патрнером.
				// Очень важно, чтобы этот ID генерировался случайным образом!
				$order->partner_order_id 	= $order->id . '_' . sprintf('%06x', rand(0, pow(2, 24)-1));
				$order->save();
			}

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

		$basket = $app->session('basket');
		if (!empty($basket)) {
			$products = Product::getAll(array('id' => array_keys($basket)), array('name' => true));
		} else {
			$products = array();
		}
		$aprtData = array(
			'pageType' => \Actionpay\APRT::PAGETYPE_PURCHASE,
			'basketProducts' => array()
		);
		foreach ($products as $product) {
			$aprtData['basketProducts'][] = array(
				'id' 		=> $product->id,
				'name' 		=> $product->name,
				'price' 	=> $product->price,
				'quantity' 	=> $basket[$product->id]
			);
		}

		// отображение формы заказа
		return $app->render('page_order', array(
			'title' => 'Оформление заказа',
			'aprtData' => $aprtData
		));
	});


	/**
	 * http://demoshop.actionpay.ru/thankyou
	 * Страница "спасибо за заказ"
	 */
	$app->page('/thankyou', function (Order $order) use ($app) {
		$aprtData = array(
			'pageType' => \Actionpay\APRT::PAGETYPE_THANKYOU,
			'purchasedProducts' => array(),
			'orderInfo' => array(
				'id' => $order->id,
				'totalPrice' => $order->getTotalPrice()
			)
		);
		foreach ($order->getOrderedProducts() as $orderProduct) {
			$aprtData['purchasedProducts'][] = array(
				'id' 		=> $orderProduct->getProduct()->id,
				'name' 		=> $orderProduct->getProduct()->name,
				'price' 	=> $orderProduct->getProduct()->price,
				'quantity' 	=> $orderProduct->count
			);
		}

		return $app->render('page_thankyou', array(
			'title' => 'Спасибо за заказ',
			'order' => $order,
			'aprtData' => $aprtData
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
		$product = $p;

		$aprtData = array(
			'pageType' => \Actionpay\APRT::PAGETYPE_PRODUCT,
			'currentProduct' => array(
				'id' 	=> $product->id,
				'name' 	=> $product->name,
				'price' => $product->price
			),
			'similarProducts' => array(),
			'currentCategory' => array(
				'id' 	=> $product->getCategory()->id,
				'name' 	=> $product->getCategory()->name,
			),
			'parentCategories' => array(),
			'childCategories' => array(),
		);
		foreach ($product->getSimilarProducts() as $similarProduct) {
			$aprtData['similarProducts'][] = array(
				'id' 	=> $similarProduct->id,
				'name' 	=> $similarProduct->name,
				'price' => $similarProduct->price
			);
		}
		foreach ($product->getCategory()->getAllParents() as $parentCategory) {
			$aprtData['parentCategories'][] = array(
				'id' 	=> $parentCategory->id,
				'name' 	=> $parentCategory->name
			);
		}
		foreach ($product->getCategory()->getChildren() as $childCategory) {
			$aprtData['childCategories'][] = array(
				'id' 	=> $childCategory->id,
				'name' 	=> $childCategory->name
			);
		}

		return $app->render('page_product', array(
			'title' => $p->name,
			'product' => $p,
			'basket' => $app->session('basket') ?: array(),
			'basketTotalItems' => $app->session('basketTotalItems') ?: 0,
			'basketTotalPrice' => $app->session('basketTotalPrice') ?: 0,
			'aprtData' => $aprtData
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

		if ($category === null) {
			$aprtData = array(
				'pageType' => \Actionpay\APRT::PAGETYPE_MAIN
			);
		} else {
			$aprtData = array(
				'pageType' => \Actionpay\APRT::PAGETYPE_CATALOG,
				'currentCategory' => array(
					'id' 	=> $category->id,
					'name' 	=> $category->name,
				),
				'parentCategories' => array(),
				'childCategories' => array(),
			);

			foreach ($category->getAllParents() as $parentCategory) {
				$aprtData['parentCategories'][] = array(
					'id' 	=> $parentCategory->id,
					'name' 	=> $parentCategory->name
				);
			}
			foreach ($category->getChildren() as $childCategory) {
				$aprtData['childCategories'][] = array(
					'id' 	=> $childCategory->id,
					'name' 	=> $childCategory->name
				);
			}
		}

		return $app->render('page_main', array(
			'title' => $category ? $category->name : 'Главная',
			'category' => $category,
			'products' => $products,
			'basket' => $app->session('basket') ?: array(),
			'basketTotalItems' => $app->session('basketTotalItems') ?: 0,
			'basketTotalPrice' => $app->session('basketTotalPrice') ?: 0,
			'aprtData' => $aprtData
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
