<?
namespace DemoShop;
/**
 * @var $product Product
 * @var $basketTotalItems int
 * @var $basketTotalPrice float
 */
?>

<aside>
	<div style="float:left">
		<i><a href="/">Все товары</a></i> &rarr;
		<? if ($product->getCategory()->getAllParents()): ?>
			<? foreach ($product->getCategory()->getAllParents() as $parentCategory): ?>
				<i><a href="/?c=<?=$parentCategory->id?>"><?= $parentCategory->name ?></a></i> &rarr;
			<? endforeach; ?>
		<? endif; ?>
		<b><?= $product->getCategory()->name ?></b>
		<? if ($product->getCategory()->getChildren()): ?>
			<ul>
			<? foreach ($product->getCategory()->getChildren() as $childCategory): ?>
				<li><a href="/?c=<?=$childCategory->id?>"><?= $childCategory->name ?></a></li>
			<? endforeach; ?>
			</ul>
		<? endif; ?>
	</div>

	<? require App::getTemplatePath('part_basket'); ?>

	<div style="clear: both"></div>
</aside>

<div class="product-page">
	<div class="product-item-image">
		<img src="/img/big/<?= $product->image ?>" alt="<?= $product->name ?>" />
	</div>
	<div class="product-item-name"><?= $product->name ?></div>
	<div class="product-item-price"><b><?= floor($product->price) ?></b><sup><?= ($product->price * 100) % 100 ?></sup> руб.</div>
	<div class="product-item-buy"><a class="btn-basket-add" href="/buy?product=<?= $product->id ?>&count=1">Купить</a></div>
	<div class="product-item-bought" <? if (!isset($basket[$product->id]) || $basket[$product->id] <= 0) { ?> style="display: none" <? } ?>>
		<span class="product-item-bought-count" data-product="<?= $product->id ?>"><?= isset($basket[$product->id]) ? $basket[$product->id] : 0 ?></span> в корзине
		<a href="/buy?product=<?= $product->id ?>&count=-1" class="btn-basket-remove" title="Убрать 1">&times;</a>
	</div>

	<div class="product-item-similar-products">
		Похожие продукты:<br/>
		<? $similar = Product::getAll(array('category_id' => $product->category_id)) ?: array(); ?>

		<? foreach ($similar as $similarProduct) if ($similarProduct->id != $product->id): ?>
			<a href="/product?p=<?= $similarProduct->id ?>" title="<?= $similarProduct->name ?>"><img src="/img/small/<?= $similarProduct->image ?>" style="zoom: 0.5"></a>
		<? endif; ?>
	</div>

	<div style="clear: both"></div>
</div>

<? require App::getTemplatePath('part_sitemap') ?>
