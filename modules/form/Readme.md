Конструктор форм
================
Эта форма работает и в php и в javascript.
Создается следующим образом

    $form = new \Wdpro\Form\Form()
    $form->add([
        'name'=>'fieldName', // Техническое название поля
        'left'=>'Описание поля', // Описание поля (может быть с разных сторон одновременно (center, left, top, right, bottom)
    ]);
Ниже рассматриваются типы полей

Картинка
--------
Позволяет загружать картинки

    $form->add([
        'type'=>$form::IMAGE, 
        resize=>[ // 
            [
                'width'=>100, // Ширина
                'height'=>100, // Высота
                'type'=>'crop' // Тип (crop) обрезать лишнее,
                'watermark'=>[ // Водяной знак
                    'file'=>WDPRO_UPLOAD_IMAGES_PATH.get_option('watermark_image'),
                    'right'=>20,
                    'bottom=>20,
                ]
            ],
            // Следующий
        ],
        'watermark'=>[ // Водяной знак
            'file'=>WDPRO_UPLOAD_IMAGES_PATH.get_option('watermark_image'),
            'right'=>20,
            'bottom=>20,
        ]
    ]);
Водяной знак может указываться как в общих настройках поля, так и в блоке 'resize'.