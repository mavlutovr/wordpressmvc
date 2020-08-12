# Yandex.Metrika

## Configure

1. Install Yandex.Metrika counter.

2. Set a counter id.

   * By settings in console.

   * Or by JavaScript:

     ```javascript
     wdpro.yandexMetrikaAddId(12345678);
     ```

## Use

https://yandex.ru/support/metrica/objects/reachgoal.html

```javascript
wdpro.yandexMetrikaGoal(target[, params[, callback[, ctx]]]);
```

## Standard goals

### Contacts form

* `contacts--start-fill` - On start fill contacts form.
* `contacts--try-to-send` - On try to send contacts form.
* `contacts--send` - On send contacts form.

### Callback form

- `callback--open` - On open callback form.
- `callback--start-fill `- On start fill callback form.
- `callback--try-to-send` - On try to send callback form.
- `callback--send` - On send callback form.

### Cart

- `cart--add-good` - On add good to cart.
