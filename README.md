![WordpressMVC Wordpress MVC](images/h1.png)

# [Wordpress MVC](https://github.com/mavlutovr/wordpressmvc)

Плагин для wordpress, который позволяет вам разрабатывать не стандартный функционал на Wordpress с помощью MVC.

По-сути вы получаете MVC-фреймворк внутри Wordpress.



## Видеообзор за 8 минут

[![](http://img.youtube.com/vi/7lLF9aMhrWA/0.jpg)](http://www.youtube.com/watch?v=7lLF9aMhrWA "")



## Несколько примеров

### Форма в админке

```php
<?php
namespace App\Gallery;

class ConsoleForm extends \App\BaseForm {

	// Инициализация полей
	protected function initFields()
	{
		// Поле загрузки картинки
		$this->add([
			'name' => 'image',
			'left' => 'Картинка',
			'type' => static::IMAGE,

			// Изменение размеров картинки
			'resize' => [

				// Большая
				[ 'width' => 1000 ],

				// Поменьше
				[
					'width' => 250,
					'height' => 200,
					'type' => 'crop',
					'dir' => 'small'
				],

			],
		]);

		// Текстовое поле
		$this->add([
			'name'=>'text',
			'left'=>'Описание картинки',
		]);

		// Кнопка "Сохранить"
		$this->add(static::SUBMIT_SAVE);
	}
}
```

### Mysql таблица

```php
<?php
namespace App\Gallery;

class SqlTable extends \App\BaseSqlTable {
	
    // Имя таблицы
	protected static $name = 'app_gallery';

	// Структура таблицы
    // (Таблица в базе меняется автоматически)
	protected static function structure()
	{
		return [

			// Поля таблицы
			static::COLLS => [
				'id',
				'menu_order'=>'int',
				'post_parent'=>'int',
				'image', // (varchar 255)
				'text'=>'text',
			],

			// Индексы
			static::INDEX => [
				'post_parent',
			],

			// Тип таблицы
			static::ENGINE => static::INNODB,
		];
	}
}
```

### Список в админке

```php
<?php
namespace App\Gallery;

class ConsoleRoll extends \Wdpro\Console\Roll {
	
    // Параметры списка
	public static function params() {
		return [
            
			'labels' => [
				'label' => 'Фотогалерея',
				'add_new' => 'Добавить фото',
			],

			'where' => [
				'WHERE `post_parent`=%d
				ORDER BY `menu_order`',
				[ $_GET['sectionId'] ]
			],

			'icon' => 'dashicons-format-gallery',
		];
	}

	// Заголовки списка
	public function templateHeaders() {
		return [
			'Фотография',
			'Подпись',
			'№ п.п.',
		];
	}

	// Строка списка
	public function template($data, $entity)
	{
		return [
            
            // Фотография
			'<img src="'
				.WDPRO_UPLOAD_IMAGES_URL
            	. 'small/' . $data['image']
				. '">',

            // Подпись
			$data['text'],

            // № п.п.
			$this->getSortingField($data),
		];
	}
}
```



## Пошаговые инструкции

- На русском
- Вместе с видео-примерами

Смотрите в разделе [Wiki](https://github.com/mavlutovr/wordpressmvc/tree/master/Wiki).



## Что плагин вам дает?

:eagle: Высокая скорость и простота разработки. Например:
* Чтобы создать форму, просто используйте класс формы. И в Php, и в Js.
* Чтобы создать SQL таблицу, просто опишите ее поля, индексы в классе. И она создастся автоматом. Что-то поменяли в массиве - поменялось в SQL-таблице.
* Ускорение и упрощение за счет MVC-подхода.
* Поддержка soy-шаблонов, less.
* И другое...

🎓 Простое и быстрое внедрение плагина в свою работу.

* За счет пошаговых инструкций (в папке [/Wiki/](https://github.com/mavlutovr/wordpressmvc/tree/master/Wiki))
* С видео-примерами
* На русском языке

🔝 Заточка под сео. То есть более высокие позиции в поисковиках.
* Есть как базовые вещи, типа редактирование \<title\>, keywords, description.
* Так и менее популярные, но важные. Например:
  * Чтобы \<title\> был в самом верху \<head\>
  * Убирать скрипты вниз.
* И другое...

:blonde_woman: Простая и очевидная работа с админкой конечным пользователем.
* То есть вашему заказчику будет проще пользоваться админкой. Чтобы добавить страницу, секретарше не надо будет объяснять, что такое "инедтификатор" или "шаблон". Она просто зайдет в "Меню сверху", нажмет "Добавить страницу" и вставит свой текст.
* От чего вы сэкономите свое время на объяснениях. А ваш заказчик будет еще больше доволен сотрудничеством с вами.

:package: При этом всем - возможность использовать Wordpress. А значит, огромное количество готовых решений. Которые помогут сэкономить вам время.



## Для каких сайтов подходит плагин Wordpress MVC?

:thumbsup: **Для корпоративных**

Вот в них он силен больше всего.

- Текстовые страницы с подразделами

- Возможность добавлять на текстовые страницы разные модули:

  - Товары

  - Фотогалереи

  - И любые другие...

    (это все легко разрабатывается)

- Адекватные языковые версии. 

  - При редактировании страницы сразу можно переключаться между языками. А не искать где-то страницу на другом языке.

  - Нужно сделать что-то переводимым на разные языки? Просто добавьте [lang] в название поля. Остальное плагин сделает за вас.

     - Создаст необходимые поля в базе
     	
     - Создаст необходимые поля в формах
     	
     - Будет выводить информацию, которая соответствует текущему языку

:ok_hand: **Для лендингов**

Тоже хорошо подходит. Особенно, когда на лендинге вам надо сделать списоки блоков, которые редактируются из админки.



## Чем нужно владеть, чтобы пользоваться плагином?

* MVC-подходом
* PHP
  * Пространства имен
  * Классы, объекты
    * В том числе статические методы
* Wordpress (не обязательно)



## Как быстро и просто решить вопрос с плагином?

Плагин сравнительно новый. И пока еще не обкатан на тысячах сайтах. Поэтому бывает надо что-то подпилить.

Для нас как разработчиков, как правило, что-то поправить гораздо проще, чем вам.

Именно поэтому, для экономии своего времени, нервов и сил. Когда возникает какой-либо вопрос по плагину. Просто напишите нам.

Мы не обещаем, что тут же решим ваш вопрос. Однако, шансы на быстрое решение велики.

### Как нам написать?

[Заполнить форму на GitHub](https://github.com/mavlutovr/wordpressmvc/issues/new)

Для более оперативного решения вопроса рекомендуем прикладывать ссылки и скриншоты.

---

На этом все.

Желаем вам интересных и прибыльных проектов.

И, пока :v:

