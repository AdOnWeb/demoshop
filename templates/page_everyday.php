<?
namespace DemoShop;
/**
 * @var $image string
 * @var $promo string
 * @var $link string
 */
?>

<div class="everyday-page">
    <div class="everyday-background"></div>
    <div class="everyday-form-wrapper">
        <h2>Свежие фрукты и овощи каждый день к завтраку</h2>
        <p>
            В нашем магазине Вы можете оформить подписку на ежедневную доставку свежих продуктов.
            <br>
            Оставьте заявку и наши менеджеры сразу свяжутся с Вами!

            <form class="everyday-form ap-crm-form" method="post">
                <p>
                    Как Вас зовут?<br>
                    <input type="text" name="name" value="Иван Иванов" />
                </p>

                <p>
                    Что Вам привозить?<br>
                    <label>
                        <input type="radio" required name="package" value="vegetables"> Овощи
                    </label>
                    <br>
                    <label>
                        <input type="radio" required name="package" value="fruits"> Фрукты
                    </label>
                    <br>
                    <label>
                        <input type="radio" required checked name="package" value="vegetables"> И то, и другое
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="bread" value="1" />
                        И еще свежеиспечённый хлеб
                    </label>
                </p>

                <p>
                    На сколько оформим подписку?<br>
                    <select name="period" required>
                        <option value="week">Неделя</option>
                        <option value="2weeks" selected>Две недели</option>
                        <option value="month">Месяц</option>
                    </select>
                </p>

                <p>
                    Ваш адрес: <br>
                    <textarea name="address" cols="50" rows="3"
                    >Москва, Ивановская улица, дом 12, кв. 34</textarea>
                </p>

                <p>
                    И телефон: <br>
                    <input type="tel" name="phone" value="+79123456789" />
                </p>

                <p>
                    <button type="submit">
                        <b>Оставить заявку</b>
                    </button>
                </p>
        </form>
        </p>
    </div>
</div>

<script>
    window.apcrm = {
        crm: "5ecfc4bfdd3ac9033d2dd163",
        form: '.everyday-form',
    }
</script>

<script src="https://apycdn.com/js/crm.js"></script>

<style>
    body {
        text-shadow: 0 0 4px white;
    }
    .everyday-background {
        background: url(/img/landing/everyday.jpg);
        background-size: cover;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: -1;
        opacity: 0.7;
    }

    .everyday-form input, select, textarea, button {
        font-family: inherit;
        font-size: inherit;
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid black;
    }

    .everyday-form-wrapper {
        background: rgba(255, 255, 255, 0.5);
        border-radius: 5px;
        padding: 20px;
        margin: 40px 0;
    }
</style>

<script>
    document.getElementsByClassName('everyday-form')[0].addEventListener('submit', function (e) {
        e.preventDefault();
        alert('Спасибо, ждите звонка!');
        return false;
    })
</script>