<?
namespace DemoShop;
/**
 * @var $phone string
 * @var $name string
 * @var $address string
 * @var $products array
 */
?>

<div>
	<h3 style="text-align: center">Администрирование магазина — оформление заказа по телефону</h3>
    <form action="/admin/phonecall" method="post">
	<table class="order-form" width="100%">
		<tr>
			<th>Номер входящего вызова</th>
			<td>
                <input type="text" name="phone" value="<?= $phone ?>" />
            </td>
		</tr>
		<tr>
			<th>Имя покупателя</th>
			<td>
                <input type="text" name="name" value="<?= $name ?>" />
            </td>
		</tr>
		<tr>
			<th>Адрес доставки</th>
			<td>
                <input type="text" name="address" value="<?= $address ?>" />
            </td>
		</tr>
        <tr>
            <th>Заказ</th>
            <td>
                <table width="100%">
                    <thead>
                        <tr>
                            <th>Товар</th>
                            <th>Цена</th>
                            <th>Кол-во</th>
                        </tr>
                    </thead>
                    <tbody>
                        <? foreach (Product::getAll([], [ 'name' => true ]) as $product): ?>
                        <tr>
                            <td><?= $product->name ?></td>
                            <td><?= $product->getPriceFormatted() ?></td>
                            <td>
                                <input style="text-align: right"
                                       type="number"
                                       name="products[<?= $product->id ?>]"
                                       min="0"
                                       value="<?= isset($products[$product->id]) ? $products[$product->id] : 0 ?>" />
                            </td>
                        </tr>
                        <? endforeach; ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <input type="submit" value="Оформить заказ" />
            </td>
        </tr>
	</table>
    </form>
</div>