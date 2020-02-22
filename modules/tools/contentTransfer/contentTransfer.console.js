wdpro.ready($ => {
	$('#js-content-transfer').each(function () {

		let stepByStep = false;

		const $container = $(this);
		const $buttonStart = $container.find('.js-start-button');
		const $buttonStop = $container.find('.js-stop-button');
		const $buttonDrop = $container.find('.js-drop-button');
		const $info = $container.find('.js-info');
		const $frameContainer = $container.find('.js-frame-container');
		const $buttonNext = $container.find('.js-next-step');

		let frameI = 1;
		let data = /*window.contentTransferStartData || */{};
		let play = false;
		let nextCallback;


		class Parser {


			/**
			 * Парсит меню
			 *
			 * @param $container {jQuery} Контейнер меню
			 * @param findA {function} Функция поиска ссылки в кнопке
			 * @param findElements {function} Функция поиска элемента в контейнере
			 * @param findContainer {function} Функция поиска контейнера подменю внутри кнопки
			 * @param prepareUrlData {function} Функция дополнительной обрабтки данных ссылки.
			 * @example prepareUrlData: url => { url['data']['image'] = '...'; return url; }
			 * @return {Array.<{ url: string, text: string, children: []}>}
			 */
			static parseUrlsSync($container,
			                     {
				                     findA = null,
				                     findElements = null,
				                     findContainer = null,
				                     prepareUrlData = null
			                     } = {}) {

				// Поиск самого меню
				if (!findContainer) findContainer = $block => $block.find('ul:first');

				// Поиск элементов
				if (!findElements) findElements = $ul => $ul.children('li');

				// Поиск ссылки
				if (!findA) findA = $li => {
					if ($li.is('a')) return $li;
					return $li.find('a').not($li.find('ul').find('a'));
				};


				let urls = [];


				// Элементы меню
				let $elements = findElements($container);
				$elements.each(function () {
					let $element = $(this);


					// Данные
					let url = {};


					// Ссылка
					let $a = findA($element);
					if ($a.length) {
						/*let href = $a.attr('href');
						if (href)
							url['url'] = href;*/


						// https://unipakspb.ru/catalog/upakowochnye_kleykie_lenty
						// https://unipakspb.ru/catalog/upakowochnye_kleykie_lenty

						// Адрес ссылки
						let link = $a.get(0);
						let href = link.protocol+"//"
							+link.host
							+link.pathname
							+link.search
							+link.hash;
						let urlParsed = wdpro.parseUrl(href);
						let linkHost = urlParsed.host.replace('www.', '');

						// Адрес страницы
						let pageUrl = data['url']['url'];
						let pageUrlParsed = wdpro.parseUrl(pageUrl);
						let pageHost = pageUrlParsed.host.replace('www.', '');

						// Добавляем ссылку, если она внутренняя
						if (linkHost === pageHost) {
							url['url'] =
								urlParsed.protocol+"//"
								+urlParsed.host
								+urlParsed.pathname
								+urlParsed.search
								+urlParsed.hash;
						}

						// Текст ссылки
						url['text'] = wdpro.trim($a.text());
					}


					// Подменю
					let $submenu = findContainer($element);
					if ($submenu) {
						let children = Parser.parseUrlsSync($submenu, {
							findContainer: findContainer,
							findA: findA,
							findElements: findElements,
							prepareUrlData: prepareUrlData
						});

						if (children)
							url['children'] = children;
					}


					if (url.text || url.children || url.url) {

						if (prepareUrlData) url = prepareUrlData(url);

						urls.push(url);
					}
				});


				if (urls.length)
					return urls;
			}


			static parsePageSync({
				                     findText,
				                     findTitle = null,
				                     findDescription = null,
				                     findKeywords = null,
				                     findH1 = null,
				                     find = null // Другие всякие штуки
			                     }) {

				// Функции по-умолчанию
				if (!findTitle) findTitle = () =>
					$(Parser.page).find('title').text();

				if (!findDescription) findDescription = () =>
					$(Parser.page).find('meta[name="description"]').attr('content');

				if (!findKeywords) findKeywords = () =>
					$(Parser.page).find('meta[name="keywords"]').attr('content');

				if (!findH1) findH1 = () =>
					$(Parser.page).find('h1:first').text();

				let data = {
					'post_content': findText(),
					'meta': {
						'title': findTitle(),
						'h1': findH1(),
						'description': findDescription(),
						'keywords': findKeywords(),
					}
				};

				if (find) {
					data = wdpro.extend(data, find());
				}

				return data;
			}


			static result(resultData) {

				console.log('Результат', resultData);

				nextStep(() => {
					wdpro.ajax(
						'contentTransferResult',
						{
							url: data['url'],
							block: data['block'],
							result: resultData
						},

						res => {
							console.log('result res', res);
							updateData(res);
							updateInfo();
							nextStep(step)
						}
					);
				});
			}


			static relativeUrlToAbsolute (relativeUrl) {

				let blockUrl = wdpro.parseUrl(data['url']['url']);

				let parsedUrl = wdpro.parseUrl(relativeUrl);
				parsedUrl.hostname = blockUrl.hostname;
				parsedUrl.protocol = blockUrl.protocol;

				return wdpro.unparseUrl(parsedUrl);
			}
		}


		const updateInfo = () => {

			if (data['finish']) {
				$info.html('Перенос материалов завершен.');
				$frameContainer.empty();
				return true;
			}

			let html = 'Блок № '+data['block']['i']+': ';

			if (data['block']) {
				html += data['block']['title'];
			}

			if (data['url']) {
				html += '<BR>';
				html += `Старница: <a href="${data['url']['url']}" target="_blank">${data['url']['url']}</a>`;
			}

			console.log('data', data);

			if (data['error']) {
				html += '<p class="wdpro-form-error">'+data['error']+'</p>';
				stop();
			}

			$info.html(html);
		};


		const updateData = res => {

			if (!res) return false;

			console.log('updateData', res);

			if (res['block']) data['block'] = res['block'];
			if (res['url']) data['url'] = res['url'];
			if (res['finish']) data['finish'] = res['finish'];

			// Когда пришел новый блок
			// Указываем стартовый адрес
			if (res['block'] && !res['url']) {
				// Убираем адрес
				delete data['url'];

				// Добавляем стартовый адрес
				if (data['block']['url']) {
					data['url'] = data['block']['url'];
				}
			}
		};


		const nextStep = callback => {
			if (stepByStep) {
				$buttonNext.show();
				nextCallback = () => {
					callback();
					$buttonNext.hide();
				};
			}

			else {
				callback();
			}
		};


		const nextStepRun = () => {
			if (nextCallback) {
				nextCallback();
				nextCallback = null;
			}
		};


		const step = () => {
			if (!play) return false;

			wdpro.ajax('contentTransferRun', res => {

				if (res['url'] && res['url']['url'])
					console.log ('run res', res['url']['url']);

				// Обновляем данные
				updateData(res);

				// Обновляем информационную строку
				updateInfo();

				// Завершение
				if (res['finish']) return false;

				loadPage(page => {

					Parser.page = $(page);

					if (!data['block'] || !data['block']['script']) {
						alert('У данного блока нет скрипта');
						return false;
					}

					eval(data['block']['script']);
				});

			});
		};


		const start = () => {
			play = true;

			if (!stepByStep) {
				$buttonStart.hide();
				$buttonStop.show();
			}

			step();
		};


		const stop = () => {
			play = false;
			$buttonStart.show();
			$buttonStop.hide();
		};


		const drop = () => {
			stop();
			$frameContainer.empty();
			data = {};
			play = false;
			$buttonDrop.loading();
			wdpro.ajax('contentTransferDrop', () => {
				$buttonDrop.loadingStop()
				$info.html('<div class="wdpro-form-error">Материалы удалены</div>');
			});
		};


		const loadPage = callback => {

			$frameContainer.empty();

			let frameKey = 'source_frame_' + frameI;
			let $frame = $('<iframe width="100%" height="400" id="js-frame" name="' + frameKey + '"></iframe>')
				.appendTo($frameContainer);

			$frame.on('load', () => {
				let doc = window.frames[frameKey].document;
				callback(doc);
			});

			$frame.attr('src', data['url']['url']);

			frameI++;
		};


		$buttonStart.on('click', () => {
			stepByStep = false;
			start();
		});
		$buttonStop.on('click', stop);
		$buttonDrop.on('click', () => {
			if (confirm('Удалить материалы?')) drop()
		});
		$buttonNext.on('click', () => {
			stepByStep = true;

			if (play)
				nextStepRun();

			else
				start();
		});
	});
});