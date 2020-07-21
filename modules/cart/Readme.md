# Корзина товаров

## Модификации

Можно сделать, чтобы в корзину можно было положить:

* Обычный товар
* Модификацию товар (определенного размера, цвета)

У разных модификаций могут быть разные цены.

Модификации основаны на ключах. Подробнее про ключи смотрите в файле [23. Ключи объектов.md](https://github.com/mavlutovr/wordpressmvc/blob/master/Wiki/23.%20%D0%9A%D0%BB%D1%8E%D1%87%D0%B8%20%D0%BE%D0%B1%D1%8A%D0%B5%D0%BA%D1%82%D0%BE%D0%B2.md)

## Класс товара

Entity.php

```php
<?php
namespace App\Catalog;

/**
 * Основная сущность модуля
 */
class Entity extends \App\BasePage implements \Wdpro\Cart\CartElementInterface {


    /**
	 * Возвращает стоимость товара за одну единицу
	 *
	 * @param null|string $key Ключ объекта товара.
	 *    В котором может содержаться помимо информации для получения самого объекта товара,
	 *    дополнительная информация, такая как цвет, размер и тд.
	 *
	 * @return float
	 */
	public function getCost($key = null): float
	{
		if ($key) {
			$key = wdpro_key_parse($key);
		}

		else {
			$key = $this->getKeyArray();
		}

		return $this->getCostForSize($key['object']['size']);
	}


    /**
	 * Возвращает данные для кнопки "Добавить в корзину"
	 *
	 * @param string|array $entityKey Ключ товара с дополнительной информацией, такой как цвет, размер и тп.
	 * @return array
	 */
	public function getDataForCartButton($entityKey)
	{
		$data = $this->getData();

        // Стоимость именно этой модификации
		$data['cost'] = $this->getCost($entityKey);

		return $data;
	}
}
```



## Кнопка "Добавить в корзину"

Это не только кнопка. Это блок, в котором может быть поле для указания количества товара в корзине. Кнопка, которая уменьшает это количество на определенное число. Это зависит от того, какой вы сделаете html-шаблон.

Кнопку можно получить с помощью метода

```php
\Wdpro\Cart\Controller::getAddToCartButton(
    $catalogEntity, // Объект товара
    $catalogModKey // Ключ товара с модификацией
);
```

Когда у товара есть модификации:

```php
<?php
namespace App\Catalog;

/**
 * Основная сущность модуля
 */
class Entity extends \App\BasePage implements \Wdpro\Cart\CartElementInterface {

	/**
	 * Обработка данных для шаблона
	 *
	 * @param array $data Необработанные данные
	 * @return array Обработанные данные
	 */
    public function prepareDataForTemplate($data)
	{
		$data['cart_buttons'] = [];
		if (is_array($data['sizes'])) {
			foreach ($data['sizes'] as $size) {
				$key = $this->getKey([ 'size' => $size['size'] ]);
				$data['cart_buttons'][] = \Wdpro\Cart\Controller::getAddToCartButton($this, $key);
			}
		}

		return $data;
	}
```



## Шаблон кнопки "В корзину"

В файле cart_add_button.php. Он создается автоматически.

#### Кнопки

```php
<button class="js-cart-control-button"
    data-delta="-100"
    >Уменьшить количество товара в корзине на 100 штук</button>
```

```php
<button class="js-cart-control-button"
    data-count="1"
    >Добавить в корзину</button>
```

```php
<button class="js-cart-control-button"
    data-remove-confirm="Удалить товар из корзины?"
    data-count="0"
    >Удалить товар из корзины</button>
```

* data-count - Сколько в итоге товара будет в корзине
* data-delta - Сколько прибавлять (убавлять) за нажатие
* data-remove-confirm - Сообщение о подтверждении удаления товара.

#### Поле с количеством товаров

Которое отображает количество товара в корзине

```php
<input type="text"
    data-step="50"
    data-min="100"
    data-remove-confirm="Удалить товар из корзины?"
    value="<?=($data['added']['count']) ? $data['added']['count'] : '0'?>"
    inputmode="numeric">
```

* data-step - Шаг, с которым добавляются товары. Может пригодится, когда это оптовый магазин и товар можно покупать по 1000 штук.
* data-min - Минимальное количество товара в корзине.
* data-remove-confirm - Сообщение о подтверждении удаления товара.



## События

#### Смена количества товара в корзине

Блок с кнопкой отправляет jQuery событие, которое можно поймать следующим образом:

```javascript
$catalogItem.on('cart-item-change', function () {
   // Обработка добавления, удаления товара из корзины, изменения количества товара в корзине
});
```

#### Изменение сводной информации о товарах в корзине

```javascript
wdpro.on('cart-summary-info', function (info) {
    // Сводные данные корзины
    console.log(info);
});
```

```php
$info = \Wdpro\Cart\Controller::getSummaryInfo();
print_r($info); // Сводные данные корзины
```

#### Инициализация блока с количеством товара

В этом блоке может быть только кнопка "В корзину". А может быть поле

```
wdpro.on('cart-item-init', function ($cartControl) {
	// Тут можно например, у цены, которая в этом блоке, поставить пробелы у тысяч (5 000)
});
```

#### Добавление товара в корзину

```php
<?php
namespace App\Catalog\Popular;

class Controller extends \App\BaseController {

	/**
	 * Выполнение скриптов после инициализаций всех модулей (на сайте)
	 */
	public static function init()
	{
		add_action('wdpro_cart_added', function ($cartRow) {
            // Выводит информацию о добавленном товаре
			print_r($cartRow);
		}
	}
```

## Фильтры

#### Сводная информация

Это может пригодиться для подсчета скидки. У вас есть количество товаров, их стоимость, другие штуки.

Исходя из них вы можете добавить в сводную информацию размер скидки.

```php
public static function runSiteStart()
{
    add_filter('wdpro_cart_summary', function ($summary) {

        // Если стоимость всех товаров больше 1000 рублей
        if ($summary['cost'] > 1000) {

            // Делаем скидку 100 рублей
            $summary['discount'] = 100;

            // Перерасчитываем итоговую стоимость
            $summary['total'] = $summary['cost'] - $summary['discount'];
        }

        return $summary;
    });
}
```

## Яндекс.Метрика

Отправляются следующие javascript-события:

- `cart--add-good` - При добавлении товара в корзину.
