# Convert large images to thubmnails with lightbox

## Enable the module

plugins/app/app.php

```php
Wdpro\Modules::addWdpro('tools/convertImgsToLightbox');
```

## Html

```html
<img
  src="URL_OF_BIG_IMAGE"
  data-lightbox="gallery"
  style="width: THUMBNAIL_WIDTHpx; height: THUMBNAIL_HTIGHTpx;"
>
```

## Ckeditor

For make `<img>` tag just by style chose.

```javascript
wdpro.ckeditor.styles.push({
  name: 'Lightbox image',
  element: 'img',
  attributes: { 'data-lightbox': 'gallery' }
});
```

Then:
1. Select an image in Ckeditor
2. Chose `Lightbox image` style
3. Dblclick on image and set it size

