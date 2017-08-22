<?
namespace DemoShop;
/**
 * @var $orders Order[]
 */
?>
<style>
	.order-list tr[data-order-status="<?= Order::STATUS_CANCELED  ?>"] td { background-color: #eeeeee; }
	.order-list tr[data-order-status="<?= Order::STATUS_CONFIRMED ?>"] td { background-color: #eeeeff; }
	.order-list tr[data-order-status="<?= Order::STATUS_DELIVERED ?>"] td { background-color: #eeffee; }
</style>

<div class="order-list">
	<h3 style="text-align: center">Администрирование магазина — список заказов</h3>
    <a href="/admin/phonecall">Принять заказ по телефону</a>
	<table width="100%" cellspacing="0">
		<thead>
			<tr>
				<th>№</th>
				<th>Дата</th>
				<th>Клиент</th>
				<th>Сумма</th>
				<th>Статус</th>
			</tr>
		</thead>
		<tbody>
		<? foreach ($orders as $order): ?>
			<tr data-order-status="<?= $order->status ?>" onclick="window.location = '/admin/order?order=<?=$order->id?>';">
				<td><?= $order->id ?></td>
				<td><?= $order->date ?></td>
				<td>
                    <?= $order->client_name ?><br>
                    <small><?= $order->getOrderedOnName() ?></small>
                </td>
				<td><?= Product::formatPrice($order->getTotalPrice()) ?></td>
				<td><nobr><?= $order->getStatusName() ?></nobr></td>
			</tr>
		<? endforeach; ?>
		</tbody>
	</table>

</div>