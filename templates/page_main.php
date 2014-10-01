<?
namespace DemoShop;
/**
 * @var $category Category
 * @var $products Product[]
 * @var $basketTotalItems int
 * @var $basketTotalPrice float
 */
?>



<aside>
	<div style="float:left">
		<? if ($category): ?>
			<i><a href="/">Все товары</a></i> &rarr;
			<? if ($category->getAllParents()): ?>
				<? foreach ($category->getAllParents() as $parentCategory): ?>
					<i><a href="/?c=<?=$parentCategory->id?>"><?= $parentCategory->name ?></a></i> &rarr;
				<? endforeach; ?>
			<? endif; ?>
			<b><?= $category->name ?></b>
			<? if ($category->getChildren()): ?>
				<ul>
				<? foreach ($category->getChildren() as $childCategory): ?>
					<li><a href="/?c=<?=$childCategory->id?>"><?= $childCategory->name ?></a></li>
				<? endforeach; ?>
				</ul>
			<? endif; ?>
		<? else: ?>
			<b>Все товары</b>
			<ul>
				<li><a href="/?c=1">Фрукты</a></li>
				<li><a href="/?c=2">Овощи</a></li>
			</ul>
		<? endif; ?>
	</div>

	<? require App::getTemplatePath('part_basket'); ?>

	<div style="clear: both"></div>
</aside>


<div class="product-list">
	<? foreach ($products as $product): ?>
	<div class="product-item">
		<div class="product-item-image">
			<a href="/product?p=<?= $product->id ?>">
			<img src="/img/small/<?= $product->image ?>" alt="<?= $product->name ?>" />
			</a>
		</div>
		<div class="product-item-price"><?= $product->getPriceFormatted() ?></sup></div>
		<div class="product-item-name">
			<a href="/product?p=<?= $product->id ?>">
			<?= $product->name ?>
			</a>
		</div>
		<div class="product-item-buy"><a class="btn-basket-add" href="/buy?product=<?= $product->id ?>&count=1">Купить</a></div>
		<div class="product-item-bought" <? if (!isset($basket[$product->id]) || $basket[$product->id] <= 0) { ?> style="display: none" <? } ?>>
			<span class="product-item-bought-count" data-product="<?= $product->id ?>"><?= isset($basket[$product->id]) ? $basket[$product->id] : 0 ?></span> в корзине
			<a href="/buy?product=<?= $product->id ?>&count=-1" class="btn-basket-remove" title="Убрать 1">&times;</a>
		</div>
	</div>
	<? endforeach; ?>
</div>

<? require App::getTemplatePath('part_sitemap') ?>
