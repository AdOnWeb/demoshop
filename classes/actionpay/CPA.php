<?php
namespace Actionpay;

class CPA {
	const COOKIENAME_PARTNER_NAME 		= 'cpa_partner';
	const COOKIENAME_TRAFFIC_IDENTIFIER = 'cpa_traffic';

	/**
	 * Номер цели Вашего оффера в Actionpay
	 * (замените на ID, который предоставит менеджер по интеграции)
	 */
	const ACTIONPAY_AIM_ID = 5850;

	/**
	 * Статусы действия в XML-отчете для Actionpay:
	 * 1 = Принято
	 * 2 = В обработке
	 * 3 = Отклолнено
	 */
	const ACTIONPAY_STATUS_ACCEPT 		= 1;
	const ACTIONPAY_STATUS_PROCESSING 	= 2;
	const ACTIONPAY_STATUS_REJECT 		= 3;

	/**
	 * Проверка параметров запроса на наличие идентификаторов перехода из CPA-сетей
	 * Возвращает true, если определен новый приведённый пользователь
	 * @return boolean
	 */
	public static function checkTraffic() {
		$partner = null;
		$traffic = null;

		// короткая форма идентификации: посетитель попадает на сайт с параметром ?actionpay=[клик].[источник]
		if (isset($_GET['actionpay'])) {
			$partner = 'actionpay';
			$traffic = $_GET['actionpay'];

		// длинная форма идентификации: посетитель попадает на сайт с параметрами ?apclick=[клик]&apsource=[источник]
		} else if (isset($_GET['apclick']) && isset($_GET['apsource'])) {
			$partner = 'actionpay';
			$traffic = $_GET['apclick'] . '.' . $_GET['apsource'];
		}

		// ... другие CPA, если вы с ними работаете

		// если параметры были определены, их требуется сохранить в cookie клиента
		if ($partner && $traffic) {
			$traffic = htmlspecialchars($traffic);

			$cookies = array(
				self::COOKIENAME_PARTNER_NAME 		=> $partner,
				self::COOKIENAME_TRAFFIC_IDENTIFIER => $traffic,
			);

			foreach ($cookies as $cookieName => $cookieValue) {
				setcookie($cookieName, $cookieValue, time() + 180 * 86400, '/');
				$_COOKIE[$cookieName] = $cookieValue;
			}

			return true;

		} else {
			return false;
		}
	}

	/**
	 * Возвращает имя последнего CPA-партнера, который привел данного пользователя
	 * @return string|null
	 */
	public static function getLastPartnerName() {
		if (isset($_COOKIE) && isset($_COOKIE[self::COOKIENAME_PARTNER_NAME])) {
			return $_COOKIE[self::COOKIENAME_PARTNER_NAME];
		}
		return null;
	}

	/**
	 * Возвращает идентификатор перехода последнего CPA-партнера, который привел данного пользователя
	 * @return string|null
	 */
	public static function getLastTrafficIdentifer() {
		if (isset($_COOKIE) && isset($_COOKIE[self::COOKIENAME_TRAFFIC_IDENTIFIER])) {
			return $_COOKIE[self::COOKIENAME_TRAFFIC_IDENTIFIER];
		}
		return null;
	}

	/**
	 * Возвращает ссылку на пиксель CPA-системы для уведомления о совершённом заказе
	 *
	 * @param $partnerName 	string	имя CPA-партнера
	 * @param $trafficId	string	идентификатор перехода
	 * @param $orderId		string	номер заказа для отслеживания CPA-системой
	 * @param $orderSum		float	сумма заказа
	 * @return string|null
	 */
	public static function getPixelUrl($partnerName, $trafficId, $orderId, $orderSum = 0.0) {
		switch ($partnerName) {
			case 'actionpay':
				$url = '//n.actionpay.ru/ok/' . self::ACTIONPAY_AIM_ID . '.png?apid=' . $orderId;
				if ($trafficId) {
					$url .= '&actionpay=' . $trafficId;
				}
				if ($orderSum) {
					$url .= '&price=' . $orderSum;
				}
				break;

			// ... другие CPA, если вы с ними работаете

			default:
				$url = null;
		}

		return $url;
	}
}