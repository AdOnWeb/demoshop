<?php
namespace Actionpay;

class APRT {
	/** Главная страница */
	const PAGETYPE_MAIN 			= 1;
	/** Страница одного товара */
	const PAGETYPE_PRODUCT         	= 2;
	/** Страница каталога/категории/подкатегории */
	const PAGETYPE_CATALOG         	= 3;
	/** Страница корзины */
	const PAGETYPE_BASKET          	= 4;
	/** Страница оформления заказа (после корзины и до страницы "спасибо за заказ") */
	const PAGETYPE_PURCHASE        	= 5;
	/** Страница оформленного заказа ("спасибо за заказ") */
	const PAGETYPE_THANKYOU        	= 6;
	/** Страница окна быстрого просмотра */
	const PAGETYPE_POPUP           	= 7;
	/** Событие добавления в корзину */
	const PAGETYPE_CART_ADD        	= 8;
	/** Событие удаления из корзины */
	const PAGETYPE_CART_REMOVE     	= 9;
	/** Событие добавления в отложенные/желаемые товары */
	const PAGETYPE_WISHLIST_ADD   	= 10;
	/** Событие удаления из отложенных/желаемых товаров */
	const PAGETYPE_WISHLIST_REMOVE	= 11;
	/** Событие нажатия на кнопку "мне нравится"/"поделиться" */
	const PAGETYPE_SHARE_BUTTON    	= 12;
	/** Другая страница (ни одна из вышеперечисленных) */
	const PAGETYPE_OTHER  			= 0;
}