<?
namespace DemoShop;

function renderCategories($startingId, $parentId = null) {
	$categories = Category::getAll($startingId ? array('id' => $startingId) : array('parent_id' => $parentId));
	if (!empty($categories)) {
		echo '<ul>';
		foreach ($categories as $category) {
			echo '<li><a href="/?c=' . $category->id . '">' . $category->name . '</a></li>';
			renderCategories(null, $category->id);
		}
		echo '</ul>';
	}
};
?>

<div class="sitemap">
	<div style="vertical-align: top; float: right">
		<ul>
			<li><a href="/">Главная</a></li>
			<li><a href="/fruits">Наши фрукты</a></li>
			<li><a href="/vegetables">Наши овощи</a></li>
			<li><a href="/basket">Корзина</a></li>
			<li><a href="/">Доставка и оплата</a></li>
			<li><a href="/">О компании</a></li>
		</ul>
	</div>

	<div>
		<? 	renderCategories(Category::FRUITS_ID); ?>
	</div>

	<div>
		<? 	renderCategories(Category::VEGETABLES_ID); ?>
	</div>
</div>