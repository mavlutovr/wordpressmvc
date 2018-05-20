# WordpressMVC
Development on wordpress by mvc.

Homepage: https://github.com/mavlutovr/wordpressmvc

Все делается путем добавления модулей.

## Тема
### Javascript
Автоматически в тему подключаются следующие файлы:

	ПАПКА_ТЕМЫ/script.js
	ПАПКА_ТЕМЫ/js/script.js

### Less
Автоматический подключаемый файл Less к теме:

	ПАПКА_ТЕМЫ/style.less
	
Этот файл автоматически компилируется в **ПАПКА_ТЕМЫ/style.less.css** и подключается к 
теме

### Soy шаблоны
Шаблоны компилируются автоматом. Для этого в теме необходимо создать папку soy и в нее 
складывать шаблоны .soy

