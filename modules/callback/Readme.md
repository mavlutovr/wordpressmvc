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

## Своя форма обратного звонка на странице

Это когда форма не открывается в диалоговом окошке, а находится прямо на странице.

```html
<form id="js-footer-callaback">

	<input type="text" name="name" required placeholder="Ваше имя">

    <input type="tel" name="phone" required placeholder="Номер телефона">

    <input type="submit" value="Перезвоните мне">

</form>
```

```javascript
wdpro.ready($ => {
	wdpro.callback.initHtmlForm(
        $('#js-footer-callaback')
    );
});
```

## Своя форма обратного звонка в диалоговом окошке

```javascript
let form = new wdpro.forms.Form();
form.add({
    'name': 'name',
    'top': 'Ваше имя',
    '*': true
});
form.add({
    'name': 'phone',
    'top': 'Ваш телефон',
    '*': true
});
form.add({
    'type': 'submit',
    'text': 'Заказать обратный звонок'
});
form.add({
    'type': 'check',
    'right': 'Я даю свое согласие на обработку персональных данных и соглашаюсь с условиями и <a href="/privacy-policy/" target="_blank">политикой конфиденциальности</a>.',
    'required': true,
    'checked': true,
    'containerClass': 'privacy-check-container'
});
wdpro.callback.setForm(form);
```


## Яндекс.Метрика

Отправляются следующие javascript-события:

- `callback--open` - При открытии формы.

- `callback--send` - При отправке формы.
