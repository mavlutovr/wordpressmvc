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

### Callback form

- `callback--open` - On open callback form.

- `callback--send` - On send callback form.

### Cart

- `cart--add-good` - On add good to cart.
