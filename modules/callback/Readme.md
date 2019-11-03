# Заказ обратного звонка

Модуль с помощью которого на сайте можно заказать обратный звонок и уведомление об
этом придет администратору на e-mail.

## Кнопка, которая открывает стандартную форму

```html
<button id="js-callback-button">
    Заказать обратный звонок
</button>
```

## Скрипт, который открывает стандартную форму

```javascript
wdpro.ready(($) => {
    wdpro.callback.openWindow();
});
```

## Своя форма обратного звонка

Это когда форма не открывается в диалоговом окошке, а находится прямо на странице.

```html
<form id="js-footer-callaback">
    
	<input type="text" name="name" required placeholder="Ваше имя">
    
    <input type="tel" name="phone" required placeholder="Номер телефона">
    
    <input type="submit" value="Перезвоните мне">

</form>
```

```javascript
wdpro.ready(($) => {
	wdpro.callback.initHtmlForm(
        $('#js-footer-callaback')
    );
});
```



