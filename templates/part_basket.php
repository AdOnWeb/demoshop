<div id="basket-info" style="float: right; text-align: right; <? if ($basketTotalItems <= 0) { ?> display: none <? } ?>" >
	Товаров в корзине: <b><span id="basket-total-items"><?= $basketTotalItems ?></span></b><br />
	На сумму: <span id="basket-total-price"><?= \DemoShop\Product::formatPrice($basketTotalPrice) ?></span><br />
	<a href="/basket" style="line-height: 2em">Перейти в корзину</a>
</div>