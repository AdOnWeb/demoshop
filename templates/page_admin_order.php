<?
namespace DemoShop;
/**
 * @var $order Order
 */
?>

<div>
	<h3 style="text-align: center">Администрирование магазина — просмотр заказа</h3>
	<table class="order-view" width="100%">
		<tr>
			<th>Заказ №</th>
			<td><?= $order->id ?></td>
		</tr>
		<tr>
			<th>Дата</th>
			<td><?= $order->date ?></td>
		</tr>
		<tr>
			<th>Клиент</th>
			<td><?= $order->client_name ?></td>
		</tr>
		<tr>
			<th>Сумма</th>
			<td><?= Product::formatPrice($order->getTotalPrice()) ?></td>
		</tr>
		<tr>
			<th>Статус</th>
			<td>
				<?= $order->getStatusName() ?>
				<? if ($order->status == Order::STATUS_UNCONFIRMED): ?>
					&rarr;
					<a href="/admin/order?order=<?=$order->id?>&status=<?=Order::STATUS_CONFIRMED?>">Подтвердить</a>
					/
					<a href="/admin/order?order=<?=$order->id?>&status=<?=Order::STATUS_CANCELED?>">Отменить</a>
				<? elseif ($order->status == Order::STATUS_CONFIRMED): ?>
					&rarr;
					<a href="/admin/order?order=<?=$order->id?>&status=<?=Order::STATUS_DELIVERED?>">Доставлен</a>
					/
					<a href="/admin/order?order=<?=$order->id?>&status=<?=Order::STATUS_CANCELED?>">Отменить</a>
				<? endif; ?>
			</td>
		</tr>
		<tr>
			<th>Состав заказа</th>
			<td>
				<table class="order-view-product-list" style="width: 100%">
					<thead>
					<tr>
						<th style="text-align: left">Товар</th>
						<th>Цена</th>
						<th>Кол-во</th>
						<th>Сумма</th>
					</tr>
					</thead>
					<tbody>
					<? foreach ($order->getOrderedProducts() as $orderProduct): ?>
						<tr>
							<td style="text-align: left">
								<a href="/product?p=<?= $orderProduct->getProduct()->id ?>">
									<img src="/img/small/<?=$orderProduct->getProduct()->image ?>" style="zoom: 0.1; vertical-align: bottom" /></a>
								<?= $orderProduct->getProduct()->name ?>
							</td>
							<td style="text-align: center">
								<?= $orderProduct->getProduct()->getPriceFormatted() ?>
							</td>
							<td style="text-align: center">
								<b><?= $orderProduct->count ?></b>
							</td>
							<td style="text-align: center">
								<?= Product::formatPrice($orderProduct->getProduct()->price * $orderProduct->count) ?>
							</td>
						</tr>

					<? endforeach; ?>
					</tbody>
				</table>
			</td>
		</tr>
	</table>

	<h4 style="text-align: left; padding: 20px"><a href="/admin">&larr; Вернуться к списку заказов</a></h4>
</div>