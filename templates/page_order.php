<?
namespace DemoShop;
/**
 * @var $basket array
 * @var $products Product[]
 * @var $basketTotalItems int
 * @var $basketTotalPrice float
 */
?>

<div class="order-form">
	<h3>Оформление заказа</h3>
	<form>
		<label>
			<i>Ваше имя</i><br/>
			<input type="text" name="name" value="Василий Пупкин" />
		</label>
		<br/><br/>
		<label>
			<i>Телефон для связи</i><br/>
			<input type="tel" name="phone" value="+7 (900) 123-45-67" />
		</label>
		<br/><br/>
		<label>
			<i>Ваш адрес</i><br/>
			<textarea name="address">481516, г. Москва, ул. Пушкина, дом 23, кв. 42</textarea>
		</label>
		<br/><br/>

		<input type="submit" value="Оформить заказ" />

		<h4 style="text-align: left; padding: 20px"><a href="/order">&larr; Вернуться к корзине</a></h4>
	</form>
</div>