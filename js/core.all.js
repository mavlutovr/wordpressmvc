var wdproData = typeof wdproData !== 'undefined' ? wdproData : null;

// Основной обеъект плагина, в который добавляются дргуие штуки
var wdpro = {
	templates: {},
	data: wdproData,
	speed: 200, // Скорость эффектов (там, там, где используется)

	// Константы
	WDPRO_TEMPLATE_URL: '',
	WDPRO_UPLOAD_IMAGES_URL: '',
	WDPRO_HOME_URL: '',
	WDPRO_UPLOAD_URL: '',

	// Номера клавиш
	KEY_ESCAPE: 27,
	KEY_ENTER: 13,
	KEY_LEFT: 37,
	KEY_UP: 38,
	KEY_RIGHT: 39,
	KEY_DOWN: 40,

	// base64_decode
	base64_decode: function (str) {
		return decodeURIComponent(escape(window.atob( str )));
	}
};


// Метод forEach для массивов
if (!Array.prototype.forEach)
{
	Array.prototype.forEach = function(fun /*, thisp */)
	{
		"use strict";

		if (this === void 0 || this === null)
			throw new TypeError();


		var t = Object(this);
		var len = t.length >>> 0;
		if (typeof fun !== "function")
			throw new TypeError();

		var thisp = arguments[1];
		for (var i = 0; i < len; i++)
		{
			if (i in t)
				fun.call(thisp, t[i], i, t);
		}
	};
}



// Проверка типа на массив
if (typeof Array.isArray === 'undefined') {
	Array.isArray = function(obj) {
		return Object.toString.call(obj) === '[object Array]';
	}
};

(function ($) {

	var readyFns = [];
	var readyInited = false;



	/**
	 * Запускает каллбэк при готовности страницы к выколнению скриптов
	 *
	 * Аналог $(document).ready()
	 *
	 * @param [n] {number} Порядковый номер выполнения. Актуален до первого срабатывания каллбков.
	 * Дальше будет срабатывать сразу
	 * @param callback {function} Каллбэк, которые запускается
	 */
	wdpro.ready = function (n, callback) {

		var args = wdpro.argumentsSortByTypes(arguments);

		if (readyInited) {
			args['function']($);
		}

		else {
			// Номер
			if (args['number'] === undefined) {
				args['number'] = 10;
			}

			readyFns.push(args);
		}

	};

	$(document).ready(function () {
		readyInited = true;

		readyFns.sort(function (a, b) {
			if (a['number'] > b['number']) {
				return 1;
			}
			if (a['number'] < b['number']) {
				return -1;
			}
			return 0;
		});

		$.each(readyFns, function (i, object) {
			object['function']($);
		});

		wdpro.body = $('body');
	});



	/**
	 * Как mktime в php
	 *
	 * http://javascript.ru/php/mktime
	 */
	wdpro.mktime = function mktime() {

		var i = 0, d = new Date(), argv = arguments, argc = argv.length;

		var dateManip = {
			0: function(tt){ return d.setHours(tt); },
			1: function(tt){ return d.setMinutes(tt); },
			2: function(tt){ return d.setSeconds(tt); },
			3: function(tt){ return d.setMonth((tt)-1); },
			4: function(tt){ return d.setDate(tt); },
			5: function(tt){ return d.setYear(tt); }
		};

		for( i = 0; i < argc; i++ ){
			if(argv[i] && isNaN(argv[i])){
				return false;
			} else if(argv[i]){
				// arg is number, let's manipulate date object
				if(!dateManip[i](argv[i])){
					// failed
					return false;
				}
			}
		}

		return Math.floor(d.getTime()/1000);
	};


	wdpro.localStorage = {


		/**
		 * Сохраняет данные в localStorage
		 * @param name {string} Имя данных
		 * @param data {*} Данные
		 */
		set: function (name, data) {

			if (data === null) {
				localStorage.removeItem(name);
			}

			else {
				localStorage.setItem(name, JSON.stringify(data));
			}
		},


		/**
		 * Загружает данные по имени
		 *
		 * @param name {string} Имя данных
		 * @return {*}
		 */
		get: function (name) {
			var json = localStorage.getItem(name);

			if (json) {
				var data = wdpro.parseJSON(json);
				return data;
			}
		}
	};


	// Yandex Metrika
	var yandexMetrikaIds = [];

	/**
	 * Добавляет счетчик Метрики
	 */
	wdpro.yandexMetrikaAddId = function (id) {
		yandexMetrikaIds.push(id);
	};


	/**
	 * Запускает функцию ym для каждого счетчика метрики
	 *
	 * @example
	 * ym(XXXXXX, 'reachGoal', target[, params[, callback[, ctx]]]);
	 *
	 * https://yandex.ru/support/metrica/objects/reachgoal.html
	 * wdpro.yandexMetrikaAddId(XXXXXX);
	 * wdpro.yandexMetrika('reachGoal', target[, params[, callback[, ctx]]]);
	 */
	wdpro.yandexMetrika = function (...args) {
		wdpro.each(yandexMetrikaIds, function (id) {
			let argsForNewMethod = wdpro.clone(args);
			let argsForOldMethod = wdpro.clone(args);
			argsForOldMethod.unshift(id);

			console.log('metrika', argsForOldMethod);

			let counterFnKey = 'yaCounter'+id;

			let counter = window[counterFnKey];
			let method = argsForNewMethod.shift();


			//console.log('counter', counter);
			//console.log('method', method);
			//console.log('args', argsForNewMethod);

			if (counter && counter[argsForOldMethod[1]]) {
				console.log('counter', method, argsForNewMethod);
				counter[method](...argsForNewMethod);
			}
			else if (window.ym) {
				ym(...argsForOldMethod);
			}
		});
	};


	/**
	 * Достижение цели
	 *
	 * https://yandex.ru/support/metrica/objects/reachgoal.html
	 *
	 * target[, params[, callback[, ctx]]]);
	 */
	wdpro.yandexMetrikaGoal = function (...args) {
		var args2 = wdpro.clone(args);
		args2.unshift('reachGoal');

		wdpro.yandexMetrika(...args2);
	};


	(function(){
		var initializing = false, fnTest = /xyz/.test(function(){xyz;}) ? /\b_super\b/ : /.*/;

		/**
		 * The base Class implementation (does nothing)
		 *
		 * @this {Class}
		 * @constructor
		 */
		this.Class = function(){};


		Class.extendMulty = function (prop1, prop2, propn)
		{
			var newClass = Class;

			wdpro.each(wdpro.args(arguments), function (prop)
			{
				newClass = newClass.extend(prop);
			});

			return newClass;
		};

		// Create a new Class that inherits from this class
		Class.extend = function(prop) {

			var _super = this.prototype;

			// Instantiate a base class (but only create the instance,
			// don't run the init constructor)
			initializing = true;
			var prototype = new this();
			initializing = false;

			// Copy the properties over onto the new prototype
			for (var name in prop) {
				// Check if we're overwriting an existing function
				prototype[name] = typeof prop[name] == "function" &&
				                  typeof _super[name] == "function" && fnTest.test(prop[name]) ?
				                  (function(name, fn){
					                  return function() {
						                  var tmp = this._super;

						                  // Add a new ._super() method that is the same method
						                  // but on the super-class
						                  this._super = _super[name];

						                  // The method only need to be bound temporarily, so we
						                  // remove it when we're done executing
						                  var ret = fn.apply(this, arguments);
						                  this._super = tmp;

						                  return ret;
					                  };
				                  })(name, prop[name]) :
				                  prop[name];
			}

			// The dummy class constructor
			function Class() {
				// All construction is actually done in the init method
				if ( !initializing && this.init )
					this.init.apply(this, arguments);
			}

			// Populate our constructed prototype object
			Class.prototype = prototype;

			// Enforce the constructor to be what we expect
			Class.prototype.constructor = Class;

			// And make this class extendable
			Class.extend = arguments.callee;

			return Class;
		};
	})();

	/**
	 * Создание класса
	 *
	 * @param prop
	 * @returns {Class}
	 */
	wdpro.Class = function (prop)
	{
		return Class.extendMulty.apply(Class, arguments);
	};


	/**
	 * Удаление значений из массива
	 *
	 * @param arr {[]} Массив
	 * @param value {*} Значение
	 */
	wdpro.arrayRemoveValue = function (arr, value)
	{
		for (var i=0; i < arr.length; i++){
			if (value == arr[i]){
				arr.splice(i, 1);
				--i;
			}
		}
	};


	/**
	 * Удаление из массива по ключу
	 *
	 * @param arr {Array} Массив
	 * @param i {number} Ключ
	 */
	wdpro.arrayRemoveByI = function (arr, i) {
		arr.splice(i, 1);
	};


	/**
	 * Удаление повторяющихся значений из массива
	 *
	 * @param array {Array} Массив с повторяющимися значениями
	 * @returns {Array}
	 */
	wdpro.arrayRemoveRepeatsValues = function (array) {

		var i = array.length, result = [];

		array.sort(function(a,b) {
			return b-a;
		});

		while(i--){
			if(result.join().search(array[i]+'\\b') == '-1') {
				result.push(array[i]);
			}
		}

		return result;
	};


	/**
	 * Возвращает количество элементов в объекте
	 *
	 * @param obj {{}} Объект
	 * @returns {number}
	 */
	wdpro.objectLength = function (obj) {

		var n = 0;

		wdpro.each(obj, function () {
			n ++;
		});

		return n;
	};


	/**
	 * Обработка контента
	 *
	 * @param content {jQuery}
	 */
	wdpro.contentProcess = function (content) {
		wdpro.trigger('content', content);
	};


	/**
	 * Установка обработчиков контента
	 *
	 * @param callback {function} Каллбэк, в который отправляется контент для обработки
	 */
	wdpro.onContent = function (callback) {
		wdpro.on('content', callback);
	};


	/**
	 * Run filter
	 *
	 * @param  {string}   filterName
	 * @param  {*}   data
	 * @param  {Function} callback
	 */
	wdpro.doFilter = (filterName, data, callback) => {

		if (!this._filters || !this._filters[filterName]) {
			callback(data);
			return;
		}

		wdpro.orderList(
			this._filters[filterName],

			(filter, next) => {
				filter(data, newData => {
					data = newData;
					next();
				});
			},

			() => callback(data)
		);
	};


	/**
	 * Add filter
	 *
	 * @param {string}   filterName
	 * @param {Function} callback
	 */
	wdpro.addFilter = (filterName, callback) => {
		this._filters = this._filters || {};
		this._filters[filterName] = this._filters[filterName] || [];

		this._filters[filterName].push(callback);
	};


	/**
	 * Запускает каллбэк для каждого элемента поочереди, после чего запускает завершающий каллбэк
	 *
	 * @param list {{}|[]|Set} Список элементов
	 * @param callbackEachElement {function} Каллбэк, принимающия элементы
	 * @param [callbackFinish] {function} Каллбэк, запускающийся в конце
	 */
	wdpro.orderList = (list, callbackEachElement, callbackFinish) => {
		wdpro.waitList (list, callbackEachElement, callbackFinish, true);
	};


	/**
	 * Запускает каллбэк для каждого элемента поочереди, после чего запускает завершающий каллбэк
	 *
	 * @param list {{}|[]|Set} Список элементов
	 * @param callbackEachElement {function} Каллбэк, принимающия элементы
	 * @param callbackFinish {function} Каллбэк, запускающийся в конце
	 * @param [oneByOne] {boolean} Поочереди
	 */
	wdpro.waitList = (list, callbackEachElement, callbackFinish, oneByOne) => {
		let waiter = new Waiter();

		if (list instanceof Set) {
			list.forEach(element => {
				waiter.add(next => callbackEachElement(element, next), oneByOne);
			});
		}

		else {
			for (let key in list) {
				let element = list[key];
				waiter.add(next => callbackEachElement(element, next), oneByOne);
			}
		}

		waiter.run(callbackFinish);
	};


	wdpro.setCookie = (cname, cvalue, exdays) => {
		exdays = exdays || 365;
		var d = new Date();
		d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
		var expires = "expires=" + d.toUTCString();
		let cookieString = cname + "=" + cvalue + ";" + expires + ";path=/";
		document.cookie = cookieString;
	};


	/**
	 * Возвращает простой объект без всяких строгих типов
	 *
	 * @param object
	 * @returns {any}
	 */
	var obj = wdpro.obj = function (object)
	{
		return object;
	};


	/**
	 * Преобразует arguments в массив
	 *
	 * @param args {arguments} Аргументы функции
	 * @param [start] {number} Индекс первого добавляемого в массив элемента
	 * @returns {Array}
	 */
	var args = wdpro.args = function (args, start)
	{
		if (start === undefined)
		{
			start = 0;
		}

		var arr = [];

		for(var i = start; i < args.length; i++)
		{
			arr.push(args[i]);
		}

		return arr;
	};


	/**
	 * Объединение объектов
	 *
	 * @param obj {{}} Объект в который копируются свойства
	 * @param objects {{}} Объекты из которых копируются свойства
	 * @returns {{}}
	 */
	var extend = wdpro.extend = function (obj, objects)
	{
		obj = obj || {};

		args(arguments).forEach(function(source) {
			if (source) {
				for (var prop in source) {
					if (source[prop])
					{
						if (source[prop].constructor === Object) {
							if (!obj[prop] || obj[prop].constructor === Object) {
								obj[prop] = obj[prop] || {};
								extend(obj[prop], source[prop]);
							} else {
								obj[prop] = source[prop];
							}
						} else {
							obj[prop] = source[prop];
						}
					}else {
						obj[prop] = source[prop];
					}
				}
			}
		});
		return obj;
	};


	/**
	 * Перебор элементов
	 *
	 * @param list {*} Список элементов
	 * @param callback {function} Каллбэк, принимающий элементы
	 */
	var each = wdpro.each = function (list, callback)
	{
		if (typeof list == 'string')
		{
			list = [list];
		}

		if (typeof list == 'object')
		{
			for(var n in list)
			{
				callback(list[n], n);
			}
		}
	};


	/**
	 * Берет из аргументов все объекты {} и объеиняет их
	 * @param arguments {*} Аргументы
	 * @param firstKey {number} Первый ключ аргументов, с которого объединять объекты аргументов
	 * @returns {{}} Объединенный объект
	 */
	var argumentsObjectsUnion = function (arguments, firstKey)
	{
		var summ = {};

		args(arguments, firstKey).forEach(function (element)
		{
			if (typeof element == 'object')
			{
				summ = extend(element, summ);
			}
		});

		return summ;
	};
	wdpro.argumentsObjectsUnion = argumentsObjectsUnion;


	/**
	 * Оаспределяет аргументы по типам
	 *
	 * @param arguments
	 * @returns {{}}
	 */
	var argumentsSortByTypes = function (arguments)
	{
		var argsByTypes = {};

		each(args(arguments), function (argument)
		{
			argsByTypes[typeof argument] = argument;
		});

		return argsByTypes;
	};
	wdpro.argumentsSortByTypes = argumentsSortByTypes;


	/**
	 * Объединяет все объекты в массиве в один
	 *
	 * @param arr {array} Массив объектов
	 * @returns {{}}
	 */
	var extendObjectsFromArray = function(arr)
	{
		var summ = {};

		each(arr, function (element)
		{
			if (typeof element == 'object')
			{
				summ = extend(element, summ);
			}
		});

		return summ;
	};
	wdpro.extendObjectsFromArray = extendObjectsFromArray;


	/**
	 * Преобразует объект в queryString
	 *
	 * @param obj {{}} Объект
	 * @param [prefix] Префикс
	 * @returns {string}
	 */
	var param = wdpro.param = function (obj, prefix)
	{
		var str = [];
		for(var p in obj) {
			var k = prefix ? prefix + "[" + p + "]" : p, v = obj[p];
			str.push(typeof v == "object" ?
			         param(v, k) :
			         encodeURIComponent(k) + "=" + encodeURIComponent(v));
		}
		return str.join("&");
	};


	/**
	 * Возвращает значение объекта по строке типа "key1.key2.key3"
	 *
	 * @param keys {string} Строка, обозначающая объект типа "key1.key2.key3"
	 * @returns {*}
	 */
	var getObjectFromStringKeys = wdpro.getObjectFromStringKeys = function (keys)
	{
		var arr = keys.split('.');

		var IF = '';
		var lastPiece = '';

		each(arr, function (key)
		{
			if (IF != '')
			{
				IF += ' && ';
			}

			var currentKey = key;
			if (lastPiece)
			{
				currentKey = lastPiece+'.'+currentKey;
			}

			IF += currentKey;

			lastPiece = currentKey;
		});

		if (eval(IF))
		{
			return eval(lastPiece);
		}
	};


	/**
	 * Вызывает функцию из объекта, находя ее по ключу типа "key1.key2.key3"
	 *
	 * @param keys {string} Ключ
	 * @param attrs {*} Атрибуты для функции
	 * @returns {*}
	 */
	var evalFnFromStringKeysObject = wdpro.evalFnFromStringKeysObject = function (keys, attrs)
	{
		var fn = getObjectFromStringKeys(keys);

		if (typeof fn == 'function')
		{
			return fn(attrs);
		}
	};



	/**
	 * Класс, который запускает каллбэк по завершению всех добавленных функций
	 */
	var Waiter = wdpro.Waiter = Class.extend({

		/**
		 * Конструктор
		 */
		init: function ()
		      {
			      // Создаем список функций
			      this.functions = new List();

			      // Данные, собранные из ожидаемых функций
			      this.data = {};
		      },


		/**
		 * Добавить функцию, выполнение которой надо дождаться
		 *
		 * @param fn {function} Функция
		 * @param [params] Параметры
		 */
		wait: function(fn, params)
		      {
			      var ob = extend({
				      fn: fn
			      }, params);

			      this.functions.push(ob);
		      },


		/**
		 * Добавить функцию, выполнение которой надо дождаться
		 *
		 * @param fn {function} Функция
		 * @param [params] Параметры
		 */
		add: function (fn, params) {
			this.wait(fn, params);
		},


		/**
		 * Запуск всех функций, завершение которых надо дождаться
		 *
		 * @param completeCallback {function} Каллбэк, срабатывающий при завершении всех функций
		 */
		onCompletion: function(completeCallback)
    {
      var self = this;

      if (completeCallback)
      {
        this.completeCallback = completeCallback;
      }

      // Если есть функции, которые надо выполнить
      if (this.functions.count() > 0)
      {
        // Остановить процесс
        var stop = false;

        /**
         * Список ожидающих функций
         */
        var runArr = new List();

        // Перечисляем функции
        this.functions.each(function (fnOb, n)
        {
          if (!stop)
          {
            // Надо ли ожидать выполнение предыдущей функции
            var wait = fnOb.wait;

            // Надо ждать и это не первая функция
            if (wait && n != 0)
            {
              stop = true;

              // Убираем из функции метку о том, что надо ждать выполнение предыдущей функции
              fnOb.wait = false;
            }

            // Не надо ждать выполнение предыдущей функции, запускаем эту
            else if (!fnOb.runned)
            {
              (function ()
              {
	              // Получаем функцию
	              var fn = fnOb.fn;

	              // Запоминаем, что функция запущена
	              fnOb.runned = true;

	              // Каллбэк, который вызывается по завершении работы функции
	              var complete = (function (data)
	              {
		              if (data && (
				              data.className
				              || data.prototype && data.prototype.className
				              || data['__proto__'] && ['__proto__'].className
			              ))
		              {
			              // Ошибка, когда в результат отправляется объект класса
			              // Так нельзя делать, потому что этот результат сливается с другими результатами
			              // И у объектов ломаются свойства
			              throw new Error('Waiter.wait() получил результат в виде объекта на основе класса ');
		              }

		              if (typeof data == 'object')
		              {
			              self.data = extend(data, self.data);
		              }

		              self.functionComplete(n);
	              });

	              // Запоминаем функцию на запуск, который произойдет после перебора всех функций
	              runArr.push({
		              fn: fn,
		              complete: complete
	              });
              })();
            }
          }
        });

        // Запуск функций после перебора
        runArr.each(function(element)
        {
          element.fn(element.complete, self.data);
        });
      }


      // Функций больше нет
      else
      {
        // Запускаем каллбэк, который ожидал их выполнение
        this.completeCallback && this.completeCallback(this.data);
      }
    },


		/**
		 * Аналог waiter.onCompletion
		 *
		 * @param callback {function}
		 */
		onFinish: function (callback) {
			this.onCompletion(callback);
		},


		/**
		 * Аналог waiter.onCompletion
		 *
		 * @param callback {function}
		 */
		run: function (callback) {
			this.onCompletion(callback);
		},


		/**
		 * Обработка завершенной функции
		 *
		 * @param n {number} Номер функции
		 */
		functionComplete: function(n) {
			                  this.functions.unset(n);

			                  this.onCompletion();
		                  }
	});


	/**
	 * Выполняет функции по порядку
	 *
	 * @param fn1 {function} Функция 1
	 * @param fn2 {function} Функция 2
	 * @param fnN {function} Функция N
	 */
	wdpro.order = function (fn1, fn2, fnN) {

		var args = wdpro.args(arguments);

		var waiter = new wdpro.Waiter();

		// Добавляем функции кроме последней
		for (var i=0; i < args.length - 1; i ++) {
			waiter.wait(args[i], true);
		}

		// Добавляем последнюю функцию
		waiter.run(args[args.length-1]);
	};


	/**
	 * Дожидается выполнения всех функций и после этого запускает последнюю
	 *
	 * @param fn1 {function} Функция 1
	 * @param fn2 {function} Функция 2
	 * @param fnN {function} Функция N
	 */
	wdpro.wait = function (fn1, fn2, fnN) {

		var args = wdpro.args(arguments);

		var waiter = new wdpro.Waiter();

		// Добавляем функции кроме последней
		for (var i=0; i < args.length - 1; i ++) {
			waiter.wait(args[i]);
		}

		// Добавляем последнюю функцию
		waiter.run(args[args.length-1]);
	};


	/**
	 *
	 */
	// export class WaiterGet
	var WaiterGet = wdpro.WaiterGet = Class.extend({


		/**
		 * Запоминает объект
		 *
		 * @param name {string} Имя, под которым объект запоминается
		 * @param [value] {*} Сам объект
		 */
		waitSet: function(name, value)
		         {
			         // Сохраняем объект
			         this.waiterGetObjects = this.waiterGetObjects || {};
			         this.waiterGetObjects[name] = value;

			         // Если есть ожидающие каллбэки
			         if (this.waiterGetCallbacks && this.waiterGetCallbacks[name])
			         {
				         // Перебираем их
				         this.waiterGetCallbacks[name].forEach(function(callback)
				         {
					         if (typeof callback == 'function')
					         {
						         callback(value);
					         }
				         });

				         // Удаляем их
				         //delete this.waiterGetCallbacks[name];
			         }
		         },


		/**
		 * Возвращает объект при наличии или появлении объекта
		 *
		 * @param name {string} Имя, под которым обхект был сохранен
		 * @param callback {function} Каллбэк, получающий обхект
		 */
		waitGet: function(name, callback)
		         {
			         // Если есть объект
			         if (this.waiterGetObjects && this.waiterGetObjects[name])
			         {
				         // Возвращаем его
				         callback(this.waiterGetObjects[name]);
			         }

			         // Иначе запоминаем каллбэк, чтобы возвратить объект в этот каллбэк при появлении обхекта
			         else
			         {
				         this.waiterGetCallbacks = this.waiterGetCallbacks || {};
				         this.waiterGetCallbacks[name] = this.waiterGetCallbacks[name] || [];
				         this.waiterGetCallbacks[name].push(callback);
			         }
		         }
	});


	/**
	 * Список элементов
	 *
	 * @this {List}
	 */
	var List = wdpro.List = Class.extend({


		init: function ()
		      {
			      // Последний немерной ключ, для добавления данных без ключа
			      this.ob = {};

			      // Текущее количество элементов в списке
			      this.lastKey = 1;

			      // Текущее количество элементов в списке
			      this._count = 0;
		      },


		/**
		 * Установка значения
		 *
		 * @param key {*} Ключ
		 * @param value {*} Данные
		 * @returns {boolean}
		 */
		set:function(key, value)
		    {
			    /*if (value === undefined)
			     {
			     value = true;
			     }*/

			    // Значение уже есть
			    if (this.isset(key))
			    {
				    var ret = false;
			    }

			    // Значения еще нет
			    else
			    {
				    var ret = true;

				    // Увеличиваем счетчик элементов
				    this._count ++;
			    }

			    this.ob[key] = {
				    value: value
			    };

			    return ret;
		    },


		/**
		 * Добавление значения в конец списка
		 *
		 * @param value {*} Значение
		 * @returns {number} Ключ значения в списке
		 */
		push: function(value)
		      {
			      // Пока есть данные по такому автоматическому ключу
			      while(this.isset(this.lastKey))
			      {
				      // Увеличиваем счетчик автоматических ключей
				      this.lastKey ++;
			      }

			      // Получаем текущий ключ, чтобы его вернуть в конце метода
			      var ret = this.lastKey;

			      // Устанавливаем значение
			      this.set(this.lastKey, value);

			      return ret;
		      },


		/**
		 * Возвращает значение, если оно существует
		 *
		 * @param key {*} Ключ значения
		 * @returns {*}
		 */
		get: function(key)
		     {
			     if (this.isset(key))
			     {
				     return this.ob[key].value;
			     }
		     },


		/**
		 * Проверяет наличие данных
		 *
		 * @param key {*} Ключ данных
		 * @returns {boolean}
		 */
		isset: function(key)
		       {
			       return this.ob[key] ? true : false;
			       //return key in this.ob ? true : false;
		       },


		/**
		 * Удаление значения по ключу
		 *
		 * @param key {*} Ключ
		 * @returns {boolean}
		 */
		unset: function(key)
		       {
			       // Если такой элемент существует
			       if (this.isset(key))
			       {
				       // Запоминаем чтобы возвратить
				       var ret = this.ob[key];

				       // Удаляем его
				       delete this.ob[key];

				       // Уменьшаем количество элементов
				       this._count --;

				       return ret.value;
			       }

			       return false;
		       },


		/**
		 * Перебор всех элементов
		 *
		 * @param callback {function} Каллбэк, получающий элементы
		 */
		each: function(callback)
		      {
			      for (var key in this.ob)
			      {
				      callback(this.ob[key].value, key);
			      }
		      },


		/**
		 * Возвращает количество элементов
		 *
		 * @returns {number}
		 */
		count: function ()
		       {
			       return this._count;
		       },


		/**
		 * Возвращает первый элемент списка
		 *
		 * @returns {*}
		 */
		getFirst: function ()
		          {
			          for (var n in this.ob)
			          {
				          return this.ob[n];
				          break;
			          }
		          },


		/**
		 * Возвращает массив всех ключей
		 *
		 * @returns {Array}
		 */
		keys: function () {
			var keys = [];

			for(var key in this.ob)
			{
				keys.push(key);
			}

			return keys;
		}
	});


	/**
	 * Класс событий для наследования другими классами
	 *
	 * @type {{}|*}
	 */
	var Event = wdpro.Event = Class.extend({


		/**
		 * Установка прослушки на событие
		 *
		 * @param actionNames {string} Имя (имена) события с пространством имен в формате name.namespace
		 * @param callback {function} Каллбэк, принимающий событие
		 * @param [params] {{ listenPrev: boolean }} Параметры
		 */
		on: function (actionNames, callback, params) {
			var self = this;

			// Загружать предыдущий, когда просто true
			if (params === true || params === 1)
			{
				params = {last: true};
			}

			// По-умолчанию
			params = extend({}, params);

			// Разбиваем строку событий на имена
			var actionNamesArr = actionNames.split(' ');

			// Перебираем имена событий
			each(actionNamesArr, function (actionName) {
				// Парсим имя
				var action = self.eventParseName(actionName);

				// Если нету массива каллбэков
				if (!self.callbacks)
				{
					// Создаем этот список
					self.callbacks = new List();
				}

				// Если нету списка каллбэков для данного действия
				if (!self.callbacks.isset(action.name))
				{
					// Создаем этот список
					self.callbacks.set(action.name, new List());
				}

				// Добавляем каллбэк в массив
				var callbackN = self.callbacks.get(action.name).push({
					fn:     callback,
					action: actionName
				});

				// Для удаления по пространству имен
				if (action.namespace)
				{
					// Если не существует списка номеров для удаления
					if (!self.removeByNamespace)
					{
						// Создаем этот список
						self.removeByNamespace = new List();
					}

					// Если не существует списка для данного действия
					if (!self.removeByNamespace.isset(actionName))
					{
						// Создаем этот список
						self.removeByNamespace.set(actionName, new List());
					}

					// Добавляем номер в список
					self.removeByNamespace.get(actionName).push(callbackN);
				}


				// Учет предыдущего события
				// Чтобы можно было добавить прослушку на событие, которое уже произошло
				if (params.listenPrev || params.prev || params.last)
				{
					// Если есть предыдущее событие
					if (self.prevEvents && self.prevEvents.isset(action.name))
					{
						// Отправляем его в каллбэк
						callback(self.prevEvents.get(action.name));
					}
				}
			});


			return this;
		},


		/**
		 * Снятие прослушки события
		 *
		 * @param actionName {string} Имя события с пространством имен в формате name.namespace
		 * @returns {Event}
		 */
		off: function (actionName) {
			var self = this;

			// Парсим имя события
			var action = this.eventParseName(actionName);

			// Удаление по пространству имен
			if (action.namespace) {
				// Если есть каллбэки для данного пространства имен
				if (this.removeByNamespace && this.removeByNamespace.isset(actionName)) {
					// Получаем список номеров каллбэков пространства имен
					var ns = this.removeByNamespace.unset(actionName);

					// Получаем список вызываемых каллбэков
					var callbacks = self.callbacks.get(action.name);

					// Если есть список вызываемых каллбэков
					if (callbacks) {
						// Перебираем номера каллбэков
						ns.each(function (n) {
							// Удаляем этот каллбэк по номеру
							callbacks.unset(n);
						});
					}
				}
			}


			// Удаление без пространства имен
			else {
				// Удаляем все каллбэки из списка каллбэков по имени события
				if (this.callbacks) {
					this.callbacks.unset(action.name);
				}

				// Удаляем инфу о каллбэках по пространствам имен
				this.removeByNamespace && this.removeByNamespace.each(function (ns, actionName) {
					// Парсим имя события
					var action = self.eventParseName(actionName);

					// Если имя совпадает с удаляемым
					if (action.name === actionName) {
						// Удаляем номера этого каллбэка из списка номеров для удаления по пространству имен
						self.removeByNamespace.unset(actionName);
					}
				});
			}


			return this;
		},


		/**
		 * Отправка событие во все прослушки этого события
		 *
		 * @param actionName {string} Имя события без пространства имен
		 * @param [data] {{}} Данные, отправляемые в прослушки события
		 * @returns {number} Количество запущенных прослушек в данный момент
		 */
		trigger: function (actionName, data) {
			// Количество запущенных каллбэков
			var ret = 0;

			// Если есть список прослушек
			if (this.callbacks) {
				// Получаем список прослушек для этого события
				var callbacks = this.callbacks.get(actionName);

				// Если есть прослушки на данное событие
				if (callbacks) {
					// Перебираем прослушки
					callbacks.each(function (callback) {
						if (typeof callback.fn === 'function') {
							// Запускаем прослушку
							callback.fn(data);

							// Увеличиваем счетчик запущенных каллбэков
							ret++;
						}
					});
				}
			}

			// Запоминаем данное событие для прослушек, которые будут установлены после,
			// но которые по параметрам будут требовать данное событие
			if (!this.prevEvents) {
				this.prevEvents = new List();
			}
			this.prevEvents.set(actionName, data);

			return ret;
		},


		/**
		 * Уствновка списка прослушек одним разом
		 *
		 * @param [list] {{}} Объект, содержащий прослушки
		 * @param [keyInList] {*} Ключ списка прослушек в этом объекте (когда объект не список прослушек, а список всяких штук, одной из штук которого является список прослушек)
		 */
		onList: function (list, keyInList) {
			var self = this;

			if (typeof list === 'object') {
				// Список каллбэков в ключе
				if (keyInList) {
					list = list[keyInList];
				}

				// Если есть такой список калббэков
				if (list && typeof list === 'object') {
					// Перебираем прослушки
					each(list, function (eventCallback, eventName) {
						// Добавляем прослушку события
						self.on(eventName, eventCallback);
					});
				}
			}
		},


		/**
		 * Разбивает имя события на имя и пространство имен
		 *
		 * @param name {string}
		 * @returns {{name: string, namespace: string}}
		 */
		eventParseName: function (name) {
			var pieces = name.split('.');

			return {
				name: pieces[0],
				namespace: pieces[1]
			};
		}
	});



	var jQueryToHtmlObjects, jQueryToHtmlObjectsId;

	/**
	 * Возвращает <span>, который потом заменится на jQuery блок
	 *
	 * Следует использовать тогда, когда мы имеем html код в виде строки
	 * и в него надо вставить jQuery элемент
	 *
	 * После того, как html строка превратится в jQuery элемент, следует запустить Seobit.jQueryToHtmlRun
	 *
	 * Алиас служит для того, чтобы после Seobit.jQueryToHtmlRun удалились все объекты, которые были добавлены
	 * по этому алиасу
	 *
	 * @param jQueryObject jQuery объект, вставляемый в html строку
	 * @param alias Алиас
	 */
	wdpro.jQueryToHtml = function (jQueryObject, alias)
	{
		// Создаем при необходимости список меток данного алиаса
		jQueryToHtmlObjects = jQueryToHtmlObjects || {};
		jQueryToHtmlObjects[alias] = jQueryToHtmlObjects[alias] || {};

		// Получаем уникальный номер метки
		jQueryToHtmlObjectsId = jQueryToHtmlObjectsId || 0;
		jQueryToHtmlObjectsId ++;

		// Запоминаем объект jQuery
		jQueryToHtmlObjects[alias][jQueryToHtmlObjectsId] = jQueryObject;

		// Возвращаем код метки
		return '<span class="JS_seobit_jquery_to_html" JS_id="'+jQueryToHtmlObjectsId+'"></span>';
	};


	/**
	 * Заменяет в блоке jQuery метки <span> на jQuery элементы, которые были добавлены в jQuery блок,
	 * когда он был html строкой
	 *
	 * @param jQueryBlock jQuery блок с метками, который ранее был html строкой
	 * @param alias {String} Алиас,
	 * метки jQuery элементы которого удаляются после расстановки jQuery элементов
	 */
	wdpro.jQueryToHtmlRun = function (jQueryBlock, alias)
	{
		if (jQueryBlock instanceof $)
		{
			/**
			 * Функция, которая находит в jQuery блоке метки и заменяет их на jQuery элементы
			 *
			 * @returns {boolean}
			 */
			var replace = function ()
			{
				// Были найдены <span> метки
				var spanExists = false;

				// Перебираем span метки
				$(jQueryBlock).find('.JS_seobit_jquery_to_html').each(function ()
				{
					// Запоминаем что были найдены <span> метки
					spanExists = true;

					// Получаем id jQuery элемента
					var id = $(this).attr('JS_id');

					// Заменяем метку на jQuery элемент
					$(this).after(jQueryToHtmlObjects[alias][id]).remove();
				});

				return spanExists;
			};

			// Заменяем метки в jQuery блоке, пока они там есть и появляются
			while(replace()) {};

			// Удаляем объекты по данному алиасу
			if (jQueryToHtmlObjects && jQueryToHtmlObjects[alias])
			{
				delete jQueryToHtmlObjects[alias];
			}
		}

		else
		{
			console.log('Не верный формат jQuery блока в jQueryToHtmlRun');
		}
	};


	// Список всех таймеров
	var timers = {};

	// Список таймеров по именам
	var timersByNames = {};

	// Последний индекс для таймера
	var lastTimersIndex = 0;

	// Таймеры еще не инициированы
	var timersInited = false;

	/**
	 * Инициализация основного таймера
	 */
	wdpro.timeInit = function ()
	{
		// Завершаем работу, если таймеры были инициированы
		if (timersInited) { return; }

		// Запускаем общий таймер
		setInterval(function()
		{
			// Перебираем все таймеры
			each(timers, function(timer, i)
			{
				// Текущее время
				var now = new Date().getTime();

				// Если время таймера подошло к запуску
				if (timer.time <= now)
				{
					// Запускаем каллбэк
					timer.callback();

					// Если каллбэк необходимо было запустить 1 раз
					if (timer.single)
					{
						// Удаляем этот таймер
						timeStop(timer.id);
					}

					// Если это повторяющийся таймер
					else
					{
						// Меняем время его следующего запуска
						timer.time += timer.interval;
					}
				}
			});

		}, 12);

		// Таймеры инициированы
		timersInited = true;
	};




	/**
	 * Запускать каллбэк через каждый промежуток времени
	 *
	 * @param [name] {string} Имя задачи
	 * @param time {number} Время
	 * @param callback {function} Каллбэк, постоянно запускающийся через заданное время
	 * @param [single] {boolean} Запустить только 1 раз
	 */
	var timeEach = function (name, time, callback, single)
	{
		var args = argumentsSortByTypes(arguments);

		// Текущее время
		var now = new Date().getTime();

		// ID таймера
		lastTimersIndex ++;

		// Имя таймера
		var name = args['string'];

		// Создаем данные таймера
		var newTimer = {
			time: now + args['number'],
			interval: args['number'],
			name: name,
			callback: args['function'],
			id: lastTimersIndex,
			single: args['boolean']
		};

		// Если есть имя таймера, запоминаем его по в этом имене
		if (name)
		{
			if (!timersByNames[name]) { timersByNames[name] = []; }
			timersByNames[name].push(lastTimersIndex);
		}

		// Добавляем таймер в общий список
		timers[lastTimersIndex] = newTimer;
	};
	wdpro.timeEach = timeEach;


	/**
	 * Запустить каллбэк 1 раз через заданный промежуток времени
	 *
	 * @param name {string} Имя задачи
	 * @param time {number} Время
	 * @param callback {function} Каллбэк, запускающийся через заданное время
	 */
	wdpro.timeOne = function (name, time, callback)
	{
		var args = argumentsSortByTypes(arguments);
		timeEach(args['string'], args['number'], args['function'], true);
	};


	/**
	 * Остановка временной задачи
	 *
	 * @param name {string|number} Имя или ID задачи
	 */
	var timeStop = wdpro.timeStop = function (id)
	{
		var ids = [];

		// По имени
		if (typeof id == 'string')
		{
			var name = id;

			if (timersByNames[name])
			{
				each(timersByNames[name], function (id)
				{
					ids.push(id);
				});

				delete timersByNames[name];
			}
		}

		// По ID
		else
		{
			ids = [id];
		}


		// Удаление
		each(ids, function (id)
		{
			delete timers[id];
		});
	};



	/**
	 * Возвращает копию объекта
	 *
	 * @param object {{}} Оригинальный объект
	 * @param [recursive] {boolean} Копировать рекурсивно
	 * @returns {{}|Array}
	 */
	var clone = wdpro.clone = function (object, recursive)
	{
		var ret;

		if (Array.isArray(object))
		{
			ret = [];
		}
		else
		{
			ret = {};
		}

		each(object, function (value, i)
		{
			if (recursive)
			{
				if (typeof value == 'object')
				{
					value = clone(value);
				}
			}
			ret[i] = value;
		});

		return ret;
	};



	/**
	 * Округление числа с заданной точностью
	 *
	 * @param number {number} Число
	 * @param accuracy {number} Точность (по-умолчанию 100)
	 * @returns {number}
	 */
	var round = wdpro.round = function (number, accuracy)
	{
		if (!accuracy)
		{
			accuracy = 100;
		}

		return Math.round(number * accuracy) / accuracy;
	};


	/**
	 * Парсинг JSON
	 *
	 * @param json {string} JSON строка
	 * @returns {*}
	 */
	var parseJSON = function (json)
	{
		try
		{
			if (typeof json === 'string')
			{
				var ob = $.parseJSON(json);
				if ((ob != null) && (ob.log != null))
				{
					console.log('Ob Log: ');
					console.log(ob.log);
				}
			}

			else if (typeof json === 'object')
			{
				ob = json;
			}
		}
		catch (err)
		{
			console.log('JSON ошибка: ', json);
			throw err;
		}

		return ob;
	};


	/**
	 *
	 * @type {parseJSON}
	 */
	wdpro.parseJSON = parseJSON;


	/**
	 * Удаляет лишние пробелы из конца и начала строки
	 *
	 * @returns {string}
	 */
	String.prototype.trim=function(){return this.replace(/^\s+|\s+$/g, '');};


	/**
	 * Удаляет лишние пробелы из конца и начала строки
	 *
	 * @param string
	 * @return {string}
	 */
	wdpro.trim = string => string.replace(/^\s+|\s+$/g, '');


	/**
	 * Удаляет лишние пробелы из всех мест строки
	 *
	 * @returns {string}
	 */
	String.prototype.fulltrim=function(){return this.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,'').replace(/\s+/g,' ');};


	/**
	 * Удаляет лишние пробелы из всех мест строки
	 *
	 * @param string
	 * @return {string}
	 */
	wdpro.fulltrim = string => string.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,'').replace(/\s+/g,' ');


	/**
	 * Делает первую букву заглавной, остальные прописные
	 *
	 * @param string
	 * @return {string}
	 */
	wdpro.upercaseFirstLetterSmallOthers = string => string[0].toUpperCase() + string.slice(1).toLowerCase();


	/**
	 * Заменяет текстовые ссылки на гипперссылки
	 *
	 * @param inputText {string} Текст с текстовыми ссылками
	 * @returns {string} Текст с гиперссылками
	 */
	wdpro.findAndReplaceLink = function (inputText) {
		function indexOf(arr, value, from) {
			for (var i = from || 0, l = (arr || []).length; i < l; i++) {
				if (arr[i] == value) return i;
			}
			return -1;
		}

		function clean(str) {
			return str ? str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;') : '';
		}

		function replaceEntities(str) {
			return se('<textarea>' + ((str || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')) + '</textarea>').value;
		}
		function se(html) {return ce('div', {innerHTML: html}).firstChild;}
		function ce(tagName, attr, style) {
			var el = document.createElement(tagName);
			if (attr) extend(el, attr);
			if (style) setStyle(el, style);
			return el;
		}
		function setStyle(elem, name, value){
			elem = ge(elem);
			if (!elem) return;
			if (typeof name == 'object') return each(name, function(k, v) { setStyle(elem,k,v); });
			if (name == 'opacity') {
				if (browser.msie) {
					if ((value + '').length) {
						if (value !== 1) {
							elem.style.filter = 'alpha(opacity=' + value * 100 + ')';
						} else {
							elem.style.filter = '';
						}
					} else {
						elem.style.cssText = elem.style.cssText.replace(/filter\s*:[^;]*/gi, '');
					}
					elem.style.zoom = 1;
				};
				elem.style.opacity = value;
			} else {
				try{
					var isN = typeof(value) == 'number';
					if (isN && (/height|width/i).test(name)) value = Math.abs(value);
					elem.style[name] = isN && !(/z-?index|font-?weight|opacity|zoom|line-?height/i).test(name) ? value + 'px' : value;
				} catch(e){debugLog('setStyle error: ', [name, value], e);}
			}
		}
		function extend() {
			var a = arguments, target = a[0] || {}, i = 1, l = a.length, deep = false, options;

			if (typeof target === 'boolean') {
				deep = target;
				target = a[1] || {};
				i = 2;
			}

			if (typeof target !== 'object' && !isFunction(target)) target = {};

			for (; i < l; ++i) {
				if ((options = a[i]) != null) {
					for (var name in options) {
						var src = target[name], copy = options[name];

						if (target === copy) continue;

						if (deep && copy && typeof copy === 'object' && !copy.nodeType) {
							target[name] = extend(deep, src || (copy.length != null ? [] : {}), copy);
						} else if (copy !== undefined) {
							target[name] = copy;
						}
					}
				}
			}

			return target;
		}

		var replacedText = (inputText || '').replace(/(^|[^A-Za-z0-9А-Яа-яёЁ\-\_])(https?:\/\/)?((?:[A-Za-z\$0-9А-Яа-яёЁ](?:[A-Za-z\$0-9\-\_А-Яа-яёЁ]*[A-Za-z\$0-9А-Яа-яёЁ])?\.?){1,5}[A-Za-z\$рфуконлайнстРФУКОНЛАЙНСТ\-\d]{2,22}(?::\d{2,5})?)((?:\/(?:(?:\&amp;|\&#33;|,[_%]|[A-Za-z0-9А-Яа-яёЁ\-\_#%?+\/\$.~=;:]+|\[[A-Za-z0-9А-Яа-яёЁ\-\_#%?+\/\$.,~=;:]*\]|\([A-Za-z0-9А-Яа-яёЁ\-\_#%?+\/\$.,~=;:]*\))*(?:,[_%]|[A-Za-z0-9А-Яа-яёЁ\-\_#%?+\/\$.~=;:]*[A-Za-z0-9А-Яа-яёЁ\_#%?+\/\$~=]|\[[A-Za-z0-9А-Яа-яёЁ\-\_#%?+\/\$.,~=;:]*\]|\([A-Za-z0-9А-Яа-яёЁ\-\_#%?+\/\$.,~=;:]*\)))?)?)/ig,
			function () { // copied to notifier.js:3401
				var matches = Array.prototype.slice.apply(arguments),
					prefix = matches[1] || '',
					protocol = matches[2] || 'http://',
					domain = matches[3] || '',
					url = domain + (matches[4] || ''),
					full = (matches[2] || '') + matches[3] + matches[4];

				domain = domain.replace(/(:\d+)/, '');

				if ((domain.indexOf('.') == -1 && domain != 'localhost') || domain.indexOf('..') != -1) return matches[0];
				var topDomain = domain.split('.').pop();
				if (topDomain.length > 6 || indexOf('info,name,aero,arpa,coop,museum,mobi,travel,xxx,asia,biz,com,net,org,gov,mil,edu,int,tel,ac,ad,ae,af,ag,ai,al,am,an,ao,aq,ar,as,at,au,aw,ax,az,ba,bb,bd,be,bf,bg,bh,bi,bj,bm,bn,bo,br,bs,bt,bv,bw,by,bz,ca,cc,cd,cf,cg,ch,ci,ck,cl,cm,cn,co,cr,cu,cv,cx,cy,cz,de,dj,dk,dm,do,dz,ec,ee,eg,eh,er,es,et,eu,fi,fj,fk,fm,fo,fr,ga,gd,ge,gf,gg,gh,gi,gl,gm,gn,gp,gq,gr,gs,gt,gu,gw,gy,hk,hm,hn,hr,ht,hu,id,ie,il,im,in,io,iq,ir,is,it,je,jm,jo,jp,ke,kg,kh,ki,km,kn,kp,kr,kw,ky,kz,la,lb,lc,li,lk,lr,ls,lt,lu,lv,ly,ma,mc,md,me,mg,mh,mk,ml,mm,mn,mo,mp,mq,mr,ms,mt,mu,mv,mw,mx,my,mz,na,nc,ne,nf,ng,ni,nl,no,np,nr,nu,nz,om,pa,pe,pf,pg,ph,pk,pl,pm,pn,pr,ps,pt,pw,py,qa,re,ro,ru,rs,rw,sa,sb,sc,sd,se,sg,sh,si,sj,sk,sl,sm,sn,so,sr,ss,st,su,sv,sx,sy,sz,tc,td,tf,tg,th,tj,tk,tl,tm,tn,to,tp,tr,tt,tv,tw,tz,ua,ug,uk,um,us,uy,uz,va,vc,ve,vg,vi,vn,vu,wf,ws,ye,yt,yu,za,zm,zw,рф,укр,сайт,онлайн,срб,cat,pro,local,localhost'.split(','), topDomain) == -1) {
					if (!/^[a-zA-Z]+$/.test(topDomain) || !matches[2]) {
						return matches[0];
					}
				}


				if (matches[0].indexOf('@') != -1) {
					return matches[0];
				}
				try {
					full = decodeURIComponent(full);
				} catch (e){}

				if (full.length > 55) {
					full = full.substr(0, 53) + '..';
				}
				full = clean(full).replace(/&amp;/g, '&');

				url = replaceEntities(url).replace(/([^a-zA-Z0-9#%;_\-.\/?&=\[\]:])/g, encodeURIComponent);
				var tryUrl = url, hashPos = url.indexOf('#/');
				if (hashPos >= 0) {
					tryUrl = url.substr(hashPos + 1);
				} else {
					hashPos = url.indexOf('#!');
					if (hashPos >= 0) {
						tryUrl = '/' + url.substr(hashPos + 2).replace(/^\//, '');
					}
				}
				return prefix + '<a href="'+ (protocol + url).replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '" target="_blank">' + full + '</a>';
			});

		return replacedText;
	};


	/**
	 * Возвращает размеры квадрата, пропорционально уменьшив их до заданной ширины
	 *
	 * @param size {{ width: number, height: number }} Размеры
	 * @param newWidth {number} Заданная ширина
	 * @returns {{ width: number, height: number }}
	 */
	wdpro.sizeToWidth = function (size, newWidth) {

		var w = size.width;
		var h = size.height;

		var n = newWidth / w;

		if (n < 1)
		{
			var W = Math.round(w * n);
			var H = Math.round(h * n);

			size.width = W;
			size.height = H;
		}

		return size;
	}


	/**
	 * Перебор элементов
	 *
	 * Отличается от $.each тем, что в первом аргументе каллбэк получает jQuery
	 * объект, а торым индекс i
	 *
	 * При этом jQuery объект готов к использованию не только по конструкции
	 * $(object).doSomething(),
	 * но и напрямую object.doSomething()
	 *
	 * @param callback {function}
	 */
	jQuery.fn.wdpro_each = function (callback)
	{
		$(this).each(function (i)
		{
			callback($(this), i);
		});

		return this;
	};


	/**
	 * Преобразует строку запроса вида "?a=A&b=B" в объект { a: "A", b: "B" }
	 *
	 * @param [queryString] {string} Строка запроса (по-умолчанию querystring в адресе
	 * браузера)
	 * @returns {{}}
	 */
	wdpro.parseQueryString = function (queryString)
	{
		queryString = queryString || window.location.search;

		var result = {}, queryString = queryString.slice(1),
			re = /([^&=]+)=([^&]*)/g, m;

		while (m = re.exec(queryString)) {
			result[decodeURIComponent(m[1])] = decodeURIComponent(m[2]);
		}

		return result;
	};


	/**
	 * Преобразует строку для вставки в QUERY_STRING
	 *
	 * @param string {string} Строка, которую надо преобразовать
	 * @return {string}
	 */
	wdpro.urlencode = function (string) {

		return encodeURI(string);

		return encodeURIComponent(string).
		// Note that although RFC3986 reserves "!", RFC5987 does not,
		// so we do not need to escape it
		replace(/['()]/g, escape). // i.e., %27 %28 %29
		replace(/\*/g, '%2A').
		// The following are not required for percent-encoding per RFC5987,
		// so we can allow for a little better readability over the wire: |`^
		replace(/%(?:7C|60|5E)/g, unescape);
	};


	/**
	 * Возвращает данные формы в виде объекта
	 *
	 * @returns {{}}
	 */
	$.fn.serializeObject = function(){

		var self = this,
			json = {},
			push_counters = {},
			patterns = {
				"validate": /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
				"key":      /[a-zA-Z0-9_]+|(?=\[\])/g,
				"push":     /^$/,
				"fixed":    /^\d+$/,
				"named":    /^[a-zA-Z0-9_]+$/
			};


		this.build = function(base, key, value){
			base[key] = value;
			return base;
		};

		this.push_counter = function(key){
			if(push_counters[key] === undefined){
				push_counters[key] = 0;
			}
			return push_counters[key]++;
		};

		$.each($(this).serializeArray(), function(){

			// skip invalid keys
			if(!patterns.validate.test(this.name)){
				return;
			}

			var k,
				keys = this.name.match(patterns.key),
				merge = this.value,
				reverse_key = this.name;

			while((k = keys.pop()) !== undefined){

				// adjust reverse_key
				reverse_key = reverse_key.replace(new RegExp("\\[" + k + "\\]$"), '');

				// push
				if(k.match(patterns.push)){
					merge = self.build([], self.push_counter(reverse_key), merge);
				}

				// fixed
				else if(false && k.match(patterns.fixed)){
					merge = self.build([], k, merge);
				}

				// named
				else if(k.match(patterns.named) || k.match(patterns.fixed)){
					//console.log(merge);
					merge = self.build({}, k, merge);
				}
			}

			json = $.extend(true, json, merge);
		});

		return json;
	};


	/**
	* Обновляет страницу
	*/
	wdpro.reloadPage = function () {
		window.location = window.location;
		console.log('window.location', window.location);
	};


	/**
	 * Возвращает адрес для ajax запроса
	 *
	 * @param  {{}|string} params Данные запроса или имя запроса
	 *
	 * @return {string}
	 */
	wdpro.ajaxUrl = function (params) {

		if (typeof params === 'string') params = { 'action': params };

		var query = wdpro.extend(params, {
				'action': 'wdpro',
				'wdproAction': params['action'],
				'lang': wdpro.data.lang,
				'get': wdpro.data.get
			});

			return this.data['ajaxUrl']+'?'+ $.param(query);
	};



	/**
	 * Выполняет ajax запрос
	 *
	 * @param $_GET_OR_ACTION {{}|string} Имя события, по которой модуль поймает этот запрос
	 * или QUERY_STRING - то, что в php будет доступно в массиве $_GET
	 * @param [$_POST] {{}} Данные - то, что в php будет доступно в массиве $_POST
	 * @param [callback] {function} каллбэк, принимающий результат запроса
	 */
	wdpro.ajax = function ($_GET_OR_ACTION, $_POST, callback) {

		var self = this;
		var url;

		// Когда указан конкретный адрес
		if (typeof $_GET_OR_ACTION === 'string'
			&& ($_GET_OR_ACTION.match(/^\/\//)
				|| $_GET_OR_ACTION.match(/^http:\/\//)
				|| $_GET_OR_ACTION.match(/^https:\/\//))
			) {
			url = $_GET_OR_ACTION;
		}

		// Когда указаны действия или еще чего
		else {
			if (typeof $_GET_OR_ACTION === 'string')
			{
				$_GET_OR_ACTION = {
					'action': $_GET_OR_ACTION
				};
			}

			var $_GET2 = wdpro.extend($_GET_OR_ACTION, {
				'action': 'wdpro',
				'wdproAction': $_GET_OR_ACTION['action'],
				'lang': wdpro.data['lang'],
				'currentPostId': wdpro.data['currentPostId'],
				'currentPostName': wdpro.data['currentPostName'],
			});

			url = this.data['ajaxUrl']+'?'+ $.param($_GET2);
		}

		if (typeof $_POST === 'function')
		{
			callback = $_POST;
			$_POST = null;
		}


		if (typeof url === 'string') {
			//url = wdpro.ajaxUrl(url);
		}

		var params = {
			'url': url,
			'type': 'POST',
			'data': $_POST,
			'success': function (json) {

				var data = self.parseJSON(json);

				if (typeof data !== 'string') {
					data = $.extend({}, data);
				}


				wdpro.trigger('ajaxData', data);

				if (data['reloadPage']) {
					wdpro.reloadPage();
				}

				if (callback)
				{
					callback(data);
				}
			},
			'error': function (error) {
				console.log(error['responseText'])
			}
		};

		$.ajax(params);
	};


	wdpro.alertAjaxError = res => {
		if (res.error) {
			alert(res.error);
			return true;
		}
	};


	/**
	 * Prepare string for use in regular expression
	 * 
	 * https://javascript.ru/php/preg_quote
	 * 
	 * @param {string} str 
	 */
	wdpro.preg_quote = str => {	// Quote regular expression characters
		// 
		// +   original by: booeyOH
		// +   improved by: Ates Goral (http://magnetiq.com)

		return str.replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/g, "\\$1");
	}



	// Глобальные события
	var wdproGlobalEvents = new wdpro.Event();

	/**
	 * Установка прослушки на событие
	 *
	 * @param actionNames {string} Имя (имена) события с пространством имен в формате name.namespace
	 * @param callback {function} Каллбэк, принимающий событие
	 * @param [params] {{ listenPrev: boolean }} Параметры
	 */
	wdpro.on = function (eventName, callback, params) {
		wdproGlobalEvents.on(eventName, callback, params);
	};


	/**
	 * Return current lang code or empty string when it's core lang
	 *
	 * @return {string}
	 */
	wdpro.lang = () => wdpro.data['lang'];


	/**
	 * Return current lang code
	 *
	 * @return {[type]} [description]
	 */
	wdpro.langNotEmpty = () => wdpro.data['langNotEmpty'];


	/**
	 * Return value of current lang from object
	 *
	 * @param  {{}} texts {{ en: '...', ru: '...' }}
	 * @return {string|*}
	 */
	wdpro.langTranslate = texts => texts[wdpro.langNotEmpty()];


	/**
	 * Отправка событие во все прослушки этого события
	 *
	 * @param actionName {string} Имя события без пространства имен
	 * @param [data] {{}} Данные, отправляемые в прослушки события
	 * @returns {number} Количество запущенных прослушек в данный момент
	 */
	wdpro.trigger = function (eventname, data) {
		wdproGlobalEvents.trigger(eventname, data);
	};


	/**
	 * Возвращает корневой url сайта (главная страница)
	 *
	 * @returns {string}
	 */
	wdpro.homeUrl = function () {
		return wdpro.data['homeUrl'];
	};


	/**
	 * Возвращает адрес папки, в которую загружаются рисунки
	 *
	 * @returns {string}
	 */
	wdpro.imagesUrl = function () {
		return wdpro.data['imagesUrl'];
	};


	/**
	 * Возвращает данные из слоя, в котором содержиться json строка
	 *
	 * @returns {{}|[]|null}
	 */
	$.fn.wdproGetJsonData = function() {

		var json = $(this).text();
		return wdpro.parseJSON(json);
	};


	/**
	 * Проверяет, телефон ли это устройство
	 *
	 * @returns {boolean}
	 */
	wdpro.isMobile = function () {
		var check = false;
		(function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
		return check;
	};


	/**
	 * Проверяет, что это либо телефон, либо планшет
	 *
	 * @returns {boolean}
	 */
	wdpro.isMobileOrTablet = function () {
		var check = false;
		(function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
		return check;
	};


	wdpro.isMacOs = () => window.navigator.platform.toUpperCase().indexOf('MAC') >= 0;


	/**
	 * Отключение нажатия на что-либо
	 *
	 * @returns {jQuery}
	 */
	jQuery.fn.wdpro_del_down = function () {
		$(this).attr("onmousedown", "return false;").mousedown(function () {
			return false;
		});

		return this;
	};


	/**
	 * Удаляет теги
	 *
	 * @param html
	 */
	wdpro.stripTags = function (html) {
		return html.replace(/<\/?[^>]+>/gi, '');
	};

	/**
	 * Декодирует html символы обратно в html код
	 * @param string
	 * @param quoteStyle
	 * @return {*}
	 */
	wdpro.htmlspecialcharsDecode = function (string, quoteStyle)
	{
		// eslint-disable-line camelcase
		//       discuss at: http://locutus.io/php/htmlspecialchars_decode/
		//      original by: Mirek Slugen
		//      improved by: Kevin van Zonneveld (http://kvz.io)
		//      bugfixed by: Mateusz "loonquawl" Zalega
		//      bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
		//      bugfixed by: Brett Zamir (http://brett-zamir.me)
		//      bugfixed by: Brett Zamir (http://brett-zamir.me)
		//         input by: ReverseSyntax
		//         input by: Slawomir Kaniecki
		//         input by: Scott Cariss
		//         input by: Francois
		//         input by: Ratheous
		//         input by: Mailfaker (http://www.weedem.fr/)
		//       revised by: Kevin van Zonneveld (http://kvz.io)
		// reimplemented by: Brett Zamir (http://brett-zamir.me)
		//        example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES')
		//        returns 1: '<p>this -> &quot;</p>'
		//        example 2: htmlspecialchars_decode("&amp;quot;")
		//        returns 2: '&quot;'
		var optTemp = 0
		var i = 0
		var noquotes = false
		if (typeof quoteStyle === 'undefined') {
			quoteStyle = 2
		}
		string = string.toString()
			.replace(/&lt;/g, '<')
			.replace(/&gt;/g, '>')
		var OPTS = {
			'ENT_NOQUOTES': 0,
			'ENT_HTML_QUOTE_SINGLE': 1,
			'ENT_HTML_QUOTE_DOUBLE': 2,
			'ENT_COMPAT': 2,
			'ENT_QUOTES': 3,
			'ENT_IGNORE': 4
		}
		if (quoteStyle === 0) {
			noquotes = true
		}
		if (typeof quoteStyle !== 'number') {
			// Allow for a single string or an array of string flags
			quoteStyle = [].concat(quoteStyle)
			for (i = 0; i < quoteStyle.length; i++) {
				// Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
				if (OPTS[quoteStyle[i]] === 0) {
					noquotes = true
				} else if (OPTS[quoteStyle[i]]) {
					optTemp = optTemp | OPTS[quoteStyle[i]]
				}
			}
			quoteStyle = optTemp
		}
		if (quoteStyle & OPTS.ENT_HTML_QUOTE_SINGLE) {
			// PHP doesn't currently escape if more than one 0, but it should:
			string = string.replace(/&#0*39;/g, "'")
			// This would also be useful here, but not a part of PHP:
			// string = string.replace(/&apos;|&#x0*27;/g, "'");
		}
		if (!noquotes) {
			string = string.replace(/&quot;/g, '"')
		}
		// Put this in last place to avoid escape being double-decoded
		string = string.replace(/&amp;/g, '&')
		return string
	};


	/**
	 * Преобразует текстовый ключ в объект, чтобы его было проще дальше использовать
	 *
	 * @param key {string|{}} Ключ
	 * @returns {{key: string, object: {}}}
	 */
	wdpro.keyParse = function (key) {

		// Когда ключ массив
		if (typeof key === 'object') {
			if (key['allReady']) {
				return key;
			}

			var infoArr = key;
			var infoString = '';

			$.each(key, function (i, value) {
				if (infoString != '')
				{
					infoString += ',';
				}
				infoString += i + ':' + value;
			});


		}


		// Ключь в виде строки
		else if (typeof key === 'string') {
			// Создаем строку ключа
			var infoString = key;

			var infoArr = {};

			var elements = infoString.split(',');

			wdpro.each(elements, function (element) {
				var elementParts = element.split(':');
				infoArr[elementParts[0]] = elementParts[1];
			});
		}

		if (infoString) {
			return {
				'key': infoString,
				'object': infoArr,
				'allReady': true
			};
		}
	};


	/**
	 * Добавляет в ключ объекта данные
	 *
	 * @param originalKey {string|{}}Оригинальный ключ
	 * @param newData {{}} Новые данные
	 * @returns {{key: string, object: {}}}
	 */
	wdpro.keyUpdate = function (originalKey, newData) {
		var key = wdpro.keyParse(originalKey);

		$.each(newData, function (newKey, newValue) {
			key['object'][newKey] = newValue;
		});

		return wdpro.keyParse(key['object']);
	};


	/**
	 * Обновляет search (query string) троку
	 *
	 * @param search {string} Строка
	 * @param values {{}} Новые данные query string
	 */
	wdpro.queryStringUpdate = function (search, values) {

		var search = $.parseParams(search);

		$.each(values, function (key, value) {
			console.log('search', key, value);

			search[key] = value;
		});

		return $.param(search);
	};


	/**
	 * Возвращает данные query string в виде объекта
	 *
	 * @returns {{}}
	 */
	wdpro.getQueryStringObject = function (queryString) {
		if (!queryString) {
			queryString = window.location.search;
		}
		return wdpro.parseParams(queryString);
	};


	/**
	 * Заменяет в адресе search строку на новую
	 *
	 * @param url {string} Url
	 * @param search {string|{}} Query String
	 */
	wdpro.replaceQueryStringInUrl = function (url, search) {
		var urlData = wdpro.parseUrl(url);

		if (typeof search === 'object') {
			search = $.param(search);
		}

		var url = urlData.protocol + '//' +
			urlData.hostname +
			urlData.pathname +
			'?' + search;

		return url;
	};


	/**
	 * Обновляет Search строку в адресе
	 *
	 * @param url {string} Адрес
	 * @param newSearchData {{}} Новые дынне в search строке
	 * @returns {string}
	 */
	wdpro.updateQueryStringInUrl = function (url, newSearchData) {
		var urlData = wdpro.parseUrl(url);
		var search = wdpro.queryStringUpdate(urlData.search, newSearchData);

		var returnUrl = urlData.protocol + '//' +
			urlData.hostname +
			urlData.pathname +
			'?' + search;

		return returnUrl;
	};


	/**
	 * Возвращает данные QueryString из адреса в виде объекта
	 *
	 * @param url {string} Url адрес
	 * @returns {{}}
	 */
	wdpro.getQueryStringDataFromUrl = function (url) {
		var urlData = wdpro.parseUrl(url);

		return $.parseParams(urlData.search);
	};


	/**
	 * Меняет адрес в браузере без перезагрузки страницы
	 *
	 * @param url {string} Url
	 */
	wdpro.changeUrlWithoutReloadPage = function (url) {
		if (window.history.replaceState) {
			window.history.replaceState(null, null, url);
		}
	};


	/**
	 *
	 * @param newQueryData
	 */
	wdpro.updateQueryString = function (newQueryData) {
		var queryStringData = wdpro.getQueryStringObject();

		wdpro.each(newQueryData, function (value, key) {
			if (value === null) {
				delete queryStringData[key];
			}
			else {
				queryStringData[key] = value;
			}
		});

		console.log('window.location.pathname', window.location.pathname);

		var queryString = $.param(queryStringData);
		var url = window.location.protocol
			+ '//' + window.location.host
			+ window.location.pathname;

		if (queryString) {
			url += '?'+queryString;
		}

		wdpro.changeUrlWithoutReloadPage(url);
	};


	/**
	 * Преобразует url в массив данных
	 *
	 * @param url {string} Url
	 * @returns {HTMLAnchorElement}
	 */
	wdpro.parseUrl = function (url) {
		if (!url) url = window.location;

		var l = document.createElement("a");
		l.href = url;
		return l;
	};


	/**
	 * Преобразует url из массива в строку
	 *
	 * @param parsedUrl {HTMLAnchorElement|{}}
	 * @return string
	 */
	wdpro.unparseUrl = function(parsedUrl) {
		return parsedUrl.protocol+"//"
		+parsedUrl.host
		+parsedUrl.pathname
		+parsedUrl.search
		+parsedUrl.hash;
	};


	/**
	 * Преобразует относительный адрес в абсолютный
	 *
	 * @param relativeUrl {string} Относительный адрес
	 * @return {string}
	 */
	wdpro.relativeUrlToAbsolute = relativeUrl =>
		wdpro.unparseUrl( wdpro.parseUrl(relativeUrl) );

	
	wdpro.debounce = (func, wait) => {
		let timeout;

		return function executedFunction(...args) {
			const later = () => {
				clearTimeout(timeout);
				func(...args);
			};

			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
		};
	};


	wdpro.copyTextFromElement = $element => {
		const element = $element.get ? $element.get(0) : $element;

		str = element.innerHTML;
		const el = document.createElement('textarea');
		el.value = str;
		document.body.appendChild(el);
		el.select();
		document.execCommand('copy');
		document.body.removeChild(el);
	};


	// https://stackoverflow.com/a/45184412/6694099
	wdpro.toNonExponential = value => {
		// if value is not a number try to convert it to number
		if (typeof value !== "number") {
			value = parseFloat(value);

			// after convert, if value is not a number return empty string
			if (isNaN(value)) {
				return "";
			}
		}

		var sign;
		var e;

		// if value is negative, save "-" in sign variable and calculate the absolute value
		if (value < 0) {
			sign = "-";
			value = Math.abs(value);
		}
		else {
			sign = "";
		}

		// if value is between 0 and 1
		if (value < 1.0) {
			// get e value
			e = parseInt(value.toString().split('e-')[1]);

			// if value is exponential convert it to non exponential
			if (e) {
				value *= Math.pow(10, e - 1);
				value = '0.' + (new Array(e)).join('0') + value.toString().substring(2);
			}
		}
		else {
			// get e value
			e = parseInt(value.toString().split('e+')[1]);

			// if value is exponential convert it to non exponential
			if (e) {
				value /= Math.pow(10, e);
				value += (new Array(e + 1)).join('0');
			}
		}

		// if value has negative sign, add to it
		return sign + value;
	}


	let originalPlaceId = 0;

	/**
	 * Добавляет элемент в конец целевого контейнера, сохраняя возможность вернуть элемент обратно на свое место
	 *
	 * Чтобы вернуть элемент обратно, используйте $(element).toOriginalPlace();
	 * @param $to {jQuery} Целевой контейнер
	 * @return {jQuery}
	 */
	$.fn.appendFromOriginalPlaceTo = function ($to) {
		$(this).each(function () {

			const element = $(this);
			saveOriginalPlace(element);


			element.appendTo($to);
		});

		return this;
	};

	/**
	 * Добавляет элемент в начало целевого контейнера, сохраняя возможность вернуть элемент обратно на свое место
	 *
	 * Чтобы вернуть элемент обратно, используйте $(element).toOriginalPlace();
	 * @param $to {jQuery} Целевой контейнер
	 * @return {jQuery}
	 */
	$.fn.prependFromOriginalPlaceTo = function ($to) {
		$(this).each(function () {

			const element = $(this);
			saveOriginalPlace(element);


			element.appendTo($to);
		});

		return this;
	};


	/**
	 * Возвращает элемент на место после таких перемещения типа $(element).appendFromOriginalPlaceTo
	 *
	 * @return {jQuery}
	 */
	$.fn.toOriginalPlace = function () {
		$(this).each(function () {

			const element = $(this);
			const id = element.data('original-place-id');
			const originalPlace = $('#js-original-place-'+id);
			originalPlace.after(element);
		});

		return this;
	};


	/**
	 * Сохраняет позицию элемента, чтобы его можно было вернуть потом сюда же
	 *
	 * @param element {jQuery}
	 */
	const saveOriginalPlace = (element) => {

		if (!element.data('original-place-id')) {
			originalPlaceId ++;

			const placeHolder = $('<div id="js-original-place-'+originalPlaceId+'"></div>').hide();
			element.data('original-place-id', originalPlaceId);

			element.after(placeHolder);
		}
	};


	/**
	 * Перераспределяет элементы по контейнерам в зависимости от ширины страницы
	 *
	 * @example
	 * <code>
	 * // Адаптивная перестановка элементов
	 * wdpro.adaptiveStructure({
	 *
	 * 	996: {
	 *
	 * 		// Шапка
	 * 		'#js-mobile-header': [
	 * 			'*', // Любой элемент, который уже есть в контейнере
	 * 			'#js-header-cart',
	 * 			'#js-callback-button'
	 * 		],
	 *
	 * 		// Меню
	 * 		'#js-mobile-menu': [
	 * 			'#js-search-form',
	 * 			'#js-header-phones',
	 * 			'#js-header-mail',
	 * 			'#js-header-schedule',
	 * 			'#js-header-address',
	 * 			'#js-aside'
	 * 		]
	 * 	},
	 *
	 *
	 * 	480: {
	 *
	 * 		// Шапка
	 * 		'#js-mobile-header': [
	 * 			'*',
	 * 			'#js-header-cart'
	 * 		],
	 *
	 * 		// Меню
	 * 		'#js-mobile-menu': [
	 * 			'#js-search-form',
	 * 			'#js-callback-button',
	 * 			'#js-header-phones',
	 * 			'#js-header-mail',
	 * 			'#js-header-schedule',
	 * 			'#js-header-address',
	 * 			'#js-aside'
	 * 		]
	 * 	}
	 *
	 * });
	 * </code>
	 *
	 * @param params
	 */
	wdpro.adaptiveStructure = (params) => {

		let elements = {};
		let lastWidth;


		const getElement = (selector) => {
			if (!elements[selector]) {
				elements[selector] = $(selector);
			}
			return elements[selector];
		};


		const rePos = () => {

			let fixI = 0;

			// Ширина окна
			let windowWidth = window.innerWidth;

			// Находим текущую ширину параметров
			let paramsWidth;
			for(let w in params) {

				// Ширина окна меньше ширины параметров
				if (windowWidth <= w) {
					if (!paramsWidth) {
						paramsWidth = w;
					}

					else {
						paramsWidth = Math.min(w, paramsWidth);
					}
				}
			}


			if (lastWidth === paramsWidth) return false;


			// Позиция из параметров
			if (paramsWidth) {

				// Получаем текущую структуру
				let structure = params[paramsWidth];


				// Перебираем контейнеры
				$.each(structure, (containerSelector, childIds) => {

					// Получаем контейнер
					let $container = getElement(containerSelector);

					// Перебираем элементы
					$.each(childIds, function (i, childSelector) {

						i -= fixI;

						// Если по этому номеру находится другой элемент
						let $currentElement = $container.children(':eq('+i+')');

						if (!$currentElement.is(childSelector)) {

							// Перед этим элементом ставим тот, который указан в параметрах
							let $targetElement = getElement(childSelector);

							if ($targetElement.length) {

								saveOriginalPlace($targetElement);

								if (i) {
									$container.children(':eq('+(i - 1)+')').after($targetElement);
								}

								else {
									$container.prepend($targetElement);
								}
							}

							else {
								fixI ++;
							}
						}
					});
				});
			}


			// Изначальная позиция
			else {
				$.each(elements, (i, $element) => {
					$element.toOriginalPlace();
				});
			}


			lastWidth = paramsWidth;
		};


		rePos();
		$(window).on('resize', rePos);
	};


	// Каллбэки на определенные размеры экрана
	{
		const list = [];

		const update = () => {
			const windowWidth = window.innerWidth;

			const inside = (params) => {

				if (typeof params.min === 'number' && params.min >= windowWidth)
					return false;

				if (typeof params.max === 'number' && params.max <= windowWidth)
					return false;

				return true;
			};

			for (let item of list) {

				// Когда каллбэк уже внутри заданного размера
				if (item.inside === null || item.inside) {

					// Если каллбэк вышел из размера
					if (!inside(item.params)) {
						item.off && item.off();
						item.inside = false;
						break;
					}
				}

				// Когда каллбэк снаружи заданного размера
				if (item.inside === null || !item.inside) {

					// Если каллбэк вошел в размер
					if (inside(item.params)) {
						item.on && item.on();
						item.inside = true;
						break;
					}
				}
			}
		};

		/**
		 * Запускает каллбэки при определенном размере окна
		 *
		 * @param params {{ [min]:number, [max]:number }}
		 * @param callbackOn {function} Запускается, когда размер окна подходит под заданный размер
		 * @param callbackOff {function} Запускается, когда размер окна не подходит под заданный размер
		 */
		wdpro.onWindowSize = (params, callbackOn, callbackOff) => {

			list.push({
				params: params,
				on: callbackOn,
				off: callbackOff,
				inside: null
			});

			update();
		};


		update();
		$(window).on('resize', update);
	}


	/**
	 * Добавляет в цену пробелы
	 *
	 * @return {jQuery} Объект, в котором находится цена
	 */
	$.fn.wdproCostSpaces = function (space) {

		if (typeof space === "undefined")
			space = ' ';

		$(this).each(function () {

			const $container = $(this);
			if ($container.is('.js-cost-inited')) return true;
			$container.addClass('js-cost-inited');

			let cost = $container.text();

			if (cost) {
				cost = cost.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1'+space);
				$container.text(cost);
			}

		});

		return this;
	};




	// Add an URL parser to JQuery that returns an object
	// This function is meant to be used with an URL like the window.location
	// Use: $.parseParams('http://mysite.com/?var=string') or $.parseParams() to parse the window.location
	// Simple variable:  ?var=abc                        returns {var: "abc"}
	// Simple object:    ?var.length=2&var.scope=123     returns {var: {length: "2", scope: "123"}}
	// Simple array:     ?var[]=0&var[]=9                returns {var: ["0", "9"]}
	// Array with index: ?var[0]=0&var[1]=9              returns {var: ["0", "9"]}
	// Nested objects:   ?my.var.is.here=5               returns {my: {var: {is: {here: "5"}}}}
	// All together:     ?var=a&my.var[]=b&my.cookie=no  returns {var: "a", my: {var: ["b"], cookie: "no"}}
	// You just cant have an object in an array, ?var[1].test=abc DOES NOT WORK
	(function ($) {
		var re = /([^&=]+)=?([^&]*)/g;
		var decode = function (str) {
			return decodeURIComponent(str.replace(/\+/g, ' '));
		};
		$.parseParams = wdpro.parseParams = function (query) {
			// recursive function to construct the result object
			function createElement(params, key, value) {
				key = key + '';
				// if the key is a property
				if (key.indexOf('.') !== -1) {
					// extract the first part with the name of the object
					var list = key.split('.');
					// the rest of the key
					var new_key = key.split(/\.(.+)?/)[1];
					// create the object if it doesnt exist
					if (!params[list[0]]) params[list[0]] = {};
					// if the key is not empty, create it in the object
					if (new_key !== '') {
						createElement(params[list[0]], new_key, value);
					} else console.warn('parseParams :: empty property in key "' + key + '"');
				} else
				// if the key is an array
				if (key.indexOf('[') !== -1) {
					// extract the array name
					var list = key.split('[');
					key = list[0];
					// extract the index of the array
					var list = list[1].split(']');
					var index = list[0]


					if (!params) params = {};
					if (!params[key]) params[key] = {};
					params[key][index] = value;
				} else
				// just normal key
				{
					if (!params) params = {};
					params[key] = value;
				}
			}
			// be sure the query is a string
			query = query + '';
			if (query === '') query = window.location + '';
			var params = {}, e;
			if (query) {
				// remove # from end of query
				if (query.indexOf('#') !== -1) {
					query = query.substr(0, query.indexOf('#'));
				}

				// remove ? at the begining of the query
				if (query.indexOf('?') !== -1) {
					query = query.substr(query.indexOf('?') + 1, query.length);
				} else return {};
				// empty parameters
				if (query == '') return {};
				// execute a createElement on every key and value
				while (e = re.exec(query)) {
					var key = decode(e[1]);
					var value = decode(e[2]);
					createElement(params, key, value);
				}
			}
			return params;
		};
	})(jQuery);

})(jQuery);

window.wdpro = wdpro;
