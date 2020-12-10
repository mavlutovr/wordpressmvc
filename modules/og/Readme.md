# Параметры социальных сетей

Этот модуль возволяет подключить og теги, которые применяются для работы кнопок "Поделиться".

## Как задать изображение

Добавить в Entity метод

```php
class Entity extends \Wdpro\BasePage {

  public function getOgImage() {
    if ($this->data['image']) {
      return WDPRO_UPLOAD_IMAGES_URL.$this->data['image'];
    }
  }
  
}
```

## Как задать тип

Добавить в Entity метод

```php
class Entity extends \Wdpro\BasePage {


	public function getOgType() {
		return 'article';
  }
  
}
```