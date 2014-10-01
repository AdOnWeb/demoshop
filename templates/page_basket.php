<?
namespace DemoShop;
/**
 * @var $basket array
 * @var $products Product[]
 * @var $basketTotalItems int
 * @var $basketTotalPrice float
 */
?>

<div class="basket-list">
	<h3>Список покупок</h3>
	<? if (!empty($basket)): ?>
		<table style="width: 100%">
			<thead>
				<tr>
					<th style="text-align: left">Товар</th>
					<th>Цена</th>
					<th>Кол-во</th>
					<th>Сумма</th>
				</tr>
			</thead>
			<tbody>
		<? foreach ($products as $product): ?>
			<tr>
				<td style="text-align: left">
					<a href="/product?p=<?= $product->id ?>"><img src="/img/small/<?=$product->image ?>" style="zoom: 0.1; vertical-align: bottom" /></a>
					<?= $product->name ?>
				</td>
				<td>
					<?= $product->getPriceFormatted() ?>
				</td>
				<td>
					<b><?= $basket[$product->id] ?></b>
				</td>
				<td>
					<?= Product::formatPrice($basket[$product->id] * $product->price) ?>
				</td>
			</tr>

		<? endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<td style="text-align: left">
						<b>Итого</b>
					</td>
					<td></td>
					<td><b><?= $basketTotalItems ?></b></td>
					<td><?= Product::formatPrice($basketTotalPrice) ?></td>
				</tr>
			</tfoot>
		</table>

		<h4 style="text-align: right; padding: 20px"><a href="/order">Перейти к оформлению заказа &rarr;</a></h4>

	<? else: ?>
		<i>В корзине пусто.</i>
	<? endif; ?>
</div>

<? require App::getTemplatePath('part_sitemap') ?>

