[Домашняя страница плагина](https://github.com/mavlutovr/wordpressmvc)

# Wordpress MVC

Плагин для wordpress, который позволяет разрабатывать не стандартный функционал на Wordpress с помощью MVC-подхода.

По сути это что-то типа фреймворка внутри Wordpress.

## Как работать сплагином?

:book: Смотрите в папке [/docs/](https://github.com/mavlutovr/wordpressmvc/tree/master/docs)

## Что плагин дает?

* :motorcycle: Высокая скорость и простота разработки
  * Например, чтобы подключить javascript, soy, css и другие файлы, достаточно их просто добавить в папку
  * Чтобы создать форму, просто используем класс формы. Как в Php, так и в Js
  * Чтобы создать SQL таблицу, просто описываем ее поля, индексы в массиве. И она создастся автоматом. Что-то поменяли в массиве - поменялось в SQL-таблице
  * Ускорение и упрощение за счет MVC-подхода
  * И другое...
* :volcano: Заточка под сео. То есть более высокие позиции в поисковиках
  * Есть как базовые вещи, типа редактирование \<title\>, keywords, description
  * Так и менее популярные, но важные. Например, 
    * Чтобы \<title\> был в самом верху \<head\>
    * Убирать скрипты вниз
  * И другое...
* :paperclip: Более простая и очевидная работа с админкой конечным пользователем
  * То есть заказчику будет проще пользоваться админкой
    * Основные вещи он поймет на интуитивном уровне
  * От чего вы сэкономите свое время на объяснениях. А заказчик будет больше доволен сотрудничеством с вами

А так же плагин поддерживает и автоматически компилирует:

* Soy шаблоны
* Less

## Для каких сайтов подходит плагин Wordpress MVC?

- :thumbsup: **Для корпоративных**

  Вот в них он силен больше всего

  - Текстовые страницы с подразделами

  - Возможность добавлять на текстовые страницы разные модули

    - Товары

    - Фотогалереи

    - И любые другие...

      (это все легко разрабатывается)
  
  - Адекватные языковые версии. 
  
  	- При редактировании страницы сразу можно переключаться между языками.
  	
  	- Нужно сделать что-то переводимым на разные языки? Просто добавьте [lang] название
  	 поля. Остальное плагин сделает за вас.
  	 	
  	 	- Создаст необходимые поля в базе
  	 	
  	 	- Создаст необходимые поля в формах
  	 	
  	 	- Будет выводить информацию, которая соответствует текущему языку

- :thinking: **Для интернет-магазинов**

  Для магазинов подходит меньше. 
  Не наша специализация. 

  Скажем так. Если есть возможность использовать WooCommerce. То лучше использовать WooCommerce.

  - Есть
    - Встроенное подключение к системам оплаты
      - Робокасса
      - Яндекс.Касса
      - PayPal
  - Нету (надо будет разрабатывать самим)
    - Корзина
    - Заказы
    - Доставка

- :ok_hand: **Для лендингов**

  Тоже в целом подходит. 
  Ничего не мешает сделать лендинг. 

  Но когда мы разрабатываем лендинг, то используем Divi. А Wordpress MVC используем когда нужно добавить не стандартный функционал.

## Для каких программистов подходит плагин?

Кто владеет:

* MVC-подходом
* PHP
	* Пространства имен
	* Классы, объекты
		* В том числе статические методы

## Зачем мы сделали этот плагин?

Сначала мы делали свои админки. И они получались классными. Быстрыми. Круто продвигались. Нравились заказчикам. Но...

Сложно было найти программиста, который бы быстро мог их освоить. Сложно было передать работу.

А так же часто приходилось изобретать велосипед. Разрабатывая то, что есть в тех же плагинах Wordpress.

Тогда мы перешли на wordpress. Потому что заодно нам требовалось разрабатывать сайты, куда можно было подключать плагины именно от wordpress.

Но разработка на wordpress оказалась не самым удобным и быстрым делом. 

Мы все же решили остановиться на Wordpress. Чтобы не изобретать постоянно велосипеды. 

А для не стандартного функционала разработать этот плагин.

И теперь мы можем:

* Быстро разрабатывать не стандартный функционал
* Стандартный, вообще, просто подключать
* Когда по каким-либо причинам мы не готовы взяться за разработку какого-нибудь функционала сами.
  * За счет использования MVC подхода этому плагину можно сравнительно быстро обучить другого специалиста
  * Мы можем поручть работу специалисту, который сделает работу вообще без использования этого плагина
  
  
## Почему плагин выгоден для заказчиков

* Простота и удобство

	* Заказчику не нужно выбирать никакие шаблоны или еще чего-нибудь. Чтобы добавить 
	страницу в верхнее меню, заходим в админке в раздел "Верхнее меню". Там нажимаем 
	кнопку "Добавить страницу". Указываем заголовок, текст, другой контент. И все :)
	
	* То есть заказчик может поручить работу с сайтом любому сотруднику. Либо спокойно 
	сможет работать с сайтом сам. Без технических навыков.
	
	* Файлы (типа фотографии) загружаются на сайт прямо с компьютера. Без медиатеки. 
	Выбрал файл, загрузил, готово.
	
* Более высокие позиции в поисковыках

	* Плагин делает всякие мелочи, которые способствуют продвижению. Например, ставит 
	title в самое начало head, добавляет rel="nofollow" и noindex к внешним ссылкам, 
	помещает в noindex скрипты и другое...
	 
	* Плагин гибок для сео. У любой страницы могут быть любые заголовки. 
	Для оптимизации страниц есть все необходимые поля. 
	
	* Даже без кешироваания сайт работает достаточно быстро. Но можно поставить 
	W3 Total Cache. Тогда сайт будет вообще летать. Что пололжительно
	влияет на продвижение.

---

На этом все.

Спасибо за внимание.

Желаю вам побольше интересных и прибыльных проектов.

И, пока :v:

