# Автоматический перенос материалов со старого сайта



Добавьте в sql таблицы модулей (SqlTable.php), куда будут копироваться материалы поле:

```php
'content_transfer_url_id'=>'int', // Для копирования материалов
```



Откройте браузер в специальном режиме

```shell
chromium-browser --disable-web-security --test-type --disable-site-isolation-trials --user-data-dir="/home/roma/Docs/tmp/Chrome"
```



### Todo

- [ ] Загрузка картинок из текста
  https://regex101.com/r/7jsRpJ/1
- [ ] Редиректы