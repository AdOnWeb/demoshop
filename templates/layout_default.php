<?
namespace DemoShop;
/**
 * @var $title string
 * @var $template string
 * @var $this App
 */
?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title><?= $title ? $title . ' - ' : '' ?>Овощи & Фрукты</title>
	<link href="http://fonts.googleapis.com/css?family=Lobster&subset=latin,cyrillic" rel="stylesheet" type="text/css">
	<link href="http://fonts.googleapis.com/css?family=Noto+Serif:400,400italic&subset=latin,cyrillic" rel="stylesheet" type="text/css">
	<link href="/css/style.css" rel="stylesheet" type="text/css">
	<script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
	<script>
		var InfoPopup = {
			show: function (content, autohide) {
				var popup = $('<div>')
					.addClass('info-popup')
					.hide()
					.html(content)
					.appendTo($('.info-popup-wrapper'))
					.fadeIn();

				if (autohide) {
					setTimeout(function () { InfoPopup.hide(popup) }, 3000);
				} else {
					$('<button>')
						.html('&times;')
						.prependTo(popup)
						.click(function () {
							InfoPopup.hide(popup);
						})
				}
			},
			hide: function (popup) {
				popup.slideUp(function () { $(this).remove() });
			}
		};

		$(function () {
			$('.btn-basket-add, .btn-basket-remove').click(function () {
				var isAdding = $(this).hasClass('btn-basket-add');
				$.get(this.href, {ajax: true}, function (data) {
					if (data && data.ok) {
						$('#basket-total-items').text(data.basketTotalItems);
						$('#basket-total-price').html(data.basketTotalPriceFormatted);
						var $count = $('.product-item-bought-count[data-product=' + data.productId + ']');
						$count.text(data.productNewCount);
						if (data.basketTotalItems > 0) {
							$('#basket-info').show();
						} else {
							$('#basket-info').hide();
						}
						if (data.productNewCount > 0) {
							$count.closest('.product-item-bought').show();
						} else {
							$count.closest('.product-item-bought').hide();
						}

						var infoText = '<b>' + data.productName + '</b> - ' +
							(isAdding ? 'добавлено в корзину' : 'убрано из корзины');

						if (data.aprtData && typeof window.APRT_SEND === 'function') {
							// Отправляем в APRT событие добавления/удаления товара
							window.APRT_SEND(data.aprtData);

							infoText += '<div class="aprt-data">' +
										'APRT_SEND(' + JSON.stringify(data.aprtData) + ')' +
										'</div>';
						}

						InfoPopup.show(infoText, true);
					}
				}, 'json');
				return false;
			})
		});
	</script>

	<?
	if ($this->infoPopupMessages) : ?>
	<script>
		$(function () {
			<? foreach ($this->infoPopupMessages as $message): ?>
			InfoPopup.show(<?= json_encode($message) ?>, false);
			<? endforeach; ?>
		});
	</script>
	<? endif; ?>

	<script type="text/javascript">
		(function() {
			var s = document.createElement('script');
			s.type = 'text/javascript';
			s.async = s.defer = true;
			s.src = '//aprtx.com/code/demoshop/';
			var p = document.getElementsByTagName('body')[0] ||
				document.getElementsByTagName('head')[0];
			if (p) p.appendChild(s);
		})();
	</script>

    <script type="text/javascript">
        (function () {
            window.APCConfig = {
                phoneWrapper: ".phone",
                phoneTemplate: "<a href=\"tel:{{99999999999}}\">{{+9 (999) 999-99-99}}</a>"
            };

            var script = document.createElement("script");
            script.type = "text/javascript";
            script.async = script.defer = true;
            script.src = "//call.actionpay.ru/js/client.js?offerId=4536";
            var p = document.getElementsByTagName("body")[0] || document.getElementsByTagName("head")[0];
            if (p) p.appendChild(script);
        })();
    </script>

	<script>
		<? $aprtDataJson = json_encode(
			isset($aprtData) ? $aprtData : array('pageType' => \Actionpay\APRT::PAGETYPE_OTHER),
			JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
		) ?>

		window.APRT_DATA = <?= $aprtDataJson ?>;
	</script>

</head>
<body>
	<div class="info-popup-wrapper"></div>

    <div class="header-wrapper">
        <div class="header-right-block">
            <a href="/admin">вход для персонала</a>
        </div>
        <div class="header-left-block">
            Телефон нашего магазина:
            <div class="phone">
                <a href="tel:+74950000000">+7 (495) 000-00-00</a>
            </div>
        </div>
        <header onclick="location.href = '/'">
            <h1>Овощи & Фрукты</h1>
            <h2>—— Интернет-магазин ——</h2>
        </header>
    </div>

	<article>
		<? require App::getTemplatePath($template); ?>
	</article>

	<footer>
        <div class="phone" style="float: right">
            +7 (495) 000-00-00
        </div>
        &copy; Магазин "Овощи и фрукты", <?= date('Y') ?>.
    </footer>

	<pre class="aprt-data">window.APRT_DATA = <?= $aprtDataJson ?>;</pre>
</body>
</html>