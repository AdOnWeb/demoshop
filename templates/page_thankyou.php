<?
namespace DemoShop;
/**
 * @var $order Order
 */
?>

<h2 style="padding-top: 20px; text-align: center">Спасибо за покупку!</h2>
<div style="text-align: center">
	<br/>
	<h4>Ваш номер заказа: <b><?= $order->id ?></b></h4>Скоро наш менеджер свяжется с Вами, чтобы уточнить время доставки.
	<br/>
	<br/>
	<? if ($order->partner_name): ?>
		<? $pixelUrl = \Actionpay\CPA::getPixelUrl(
			$order->partner_name,
			$order->partner_traffic_id,
			$order->partner_order_id,
			$order->getTotalPrice()
		) ?>
		<pre><?= $pixelUrl ?></pre>
		<img src="<?= $pixelUrl ?>" width="0" height="0" />
	<? endif; ?>
</div>