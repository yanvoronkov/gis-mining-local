/**
 * Менеджер просмотренных статей через LocalStorage
 */
class ViewedArticlesManager {
	constructor() {
		this.storageKey = 'viewed_articles';
		this.maxArticles = 10;
		this.currentArticleId = null;
		this.currentArticleData = null;
		this.currentArticleUrl = '';
		this.baseUrl = '/our-blog/';
		this.sectionType = 'blog';

		// Кэшируем DOM элементы для быстрого доступа
		this.domCache = {};

		this.init();
	}

	/**
 * Инициализация компонента
 */
	init() {
		// Кэшируем DOM элементы
		this.cacheDOMElements();

		// Получаем данные текущей статьи
		this.currentArticleId = this.getCurrentArticleId();
		this.currentArticleData = this.getCurrentArticleData();
		this.currentArticleUrl = this.getCurrentArticleUrl();
		this.baseUrl = this.getBaseUrl();
		this.sectionType = this.getSectionType();

		// Если есть данные о текущей статье, добавляем её в просмотренные
		if (this.currentArticleId && this.currentArticleData) {
			this.addArticle(this.currentArticleId, this.currentArticleData);
		}

		// Сразу обновляем UI без ожидания слайдера
		this.updateViewedArticlesUI();

		// Добавляем обработчики событий
		this.addEventListeners();
	}

	/**
	 * Кэширование DOM элементов для быстрого доступа
	 */
	cacheDOMElements() {
		this.domCache = {
			currentArticleId: document.getElementById('currentArticleId'),
			currentArticleName: document.getElementById('currentArticleName'),
			currentArticleCode: document.getElementById('currentArticleCode'),
			currentArticlePreviewText: document.getElementById('currentArticlePreviewText'),
			currentArticlePreviewPicture: document.getElementById('currentArticlePreviewPicture'),
			currentArticleActiveFrom: document.getElementById('currentArticleActiveFrom'),
			currentArticleTags: document.getElementById('currentArticleTags'),
			baseUrl: document.getElementById('baseUrl'),
			sectionType: document.getElementById('sectionType'),
			currentArticleUrl: document.getElementById('currentArticleUrl'),
			viewedArticlesList: document.getElementById('viewedArticlesList'),
			viewedArticlesContainer: document.getElementById('viewedArticlesContainer')
		};
	}

	/**
	 * Получение ID текущей статьи
	 */
	getCurrentArticleId() {
		return this.domCache.currentArticleId ? this.domCache.currentArticleId.value : null;
	}

	/**
	 * Получение базового URL раздела
	 */
	getBaseUrl() {
		return this.domCache.baseUrl ? this.domCache.baseUrl.value : '/our-blog/';
	}

	/**
	 * Получение типа раздела
	 */
	getSectionType() {
		return this.domCache.sectionType ? this.domCache.sectionType.value : 'blog';
	}

	/**
	 * Получение URL текущей статьи
	 */
	getCurrentArticleUrl() {
		return this.domCache.currentArticleUrl ? this.domCache.currentArticleUrl.value : '';
	}

	/**
 * Получение данных текущей статьи
 */
	getCurrentArticleData() {
		if (!this.domCache.currentArticleName || !this.domCache.currentArticleCode) return null;

		return {
			ID: this.getCurrentArticleId(),
			NAME: this.domCache.currentArticleName.value,
			CODE: this.domCache.currentArticleCode.value,
			URL: this.getCurrentArticleUrl(),
			PREVIEW_TEXT: this.domCache.currentArticlePreviewText?.value || '',
			PREVIEW_PICTURE: {
				SRC: this.domCache.currentArticlePreviewPicture?.value || ''
			},
			ACTIVE_FROM: this.domCache.currentArticleActiveFrom?.value || '',
			PROPERTY_TAGS_VALUE: (() => {
				try {
					return this.domCache.currentArticleTags ? JSON.parse(this.domCache.currentArticleTags.value) : [];
				} catch {
					return [];
				}
			})()
		};
	}

	/**
	 * Добавление статьи в просмотренные
	 */
	addArticle(articleId, articleData) {
		if (!articleId || !articleData) return;

		const viewedArticles = this.getViewedArticles();

		// Проверяем, есть ли уже эта статья
		const existingIndex = viewedArticles.findIndex(article => article.ID == articleId);

		if (existingIndex !== -1) {
			// Удаляем существующую статью
			viewedArticles.splice(existingIndex, 1);
		}

		// Добавляем метку времени
		const articleWithTimestamp = {
			...articleData,
			viewed_at: new Date().toISOString()
		};

		// Добавляем в начало массива
		viewedArticles.unshift(articleWithTimestamp);

		// Ограничиваем количество
		if (viewedArticles.length > this.maxArticles) {
			viewedArticles.splice(this.maxArticles);
		}

		// Сохраняем в LocalStorage
		this.saveToLocalStorage(viewedArticles);
	}

	/**
	 * Получение просмотренных статей
	 */
	getViewedArticles() {
		try {
			const data = localStorage.getItem(this.storageKey);
			return data ? JSON.parse(data) : [];
		} catch (error) {
			console.error('Ошибка чтения из LocalStorage:', error);
			return [];
		}
	}

	/**
	 * Сохранение в LocalStorage
	 */
	saveToLocalStorage(articles) {
		try {
			localStorage.setItem(this.storageKey, JSON.stringify(articles));
		} catch (error) {
			console.error('Ошибка сохранения в LocalStorage:', error);
		}
	}

	/**
	 * Исключение текущей статьи из списка
	 */
	excludeCurrentArticle(articles) {
		if (!this.currentArticleId) return articles;

		return articles.filter(article => article.ID != this.currentArticleId);
	}

	/**
 * Обновление UI компонента
 */
	updateViewedArticlesUI() {
		const container = this.domCache.viewedArticlesList;
		const containerElement = this.domCache.viewedArticlesContainer;

		if (!container || !containerElement) return;

		const viewedArticles = this.getViewedArticles();
		const filteredArticles = this.excludeCurrentArticle(viewedArticles);

		// Очищаем контейнер
		container.innerHTML = '';

		// Если нет просмотренных статей, скрываем компонент
		if (filteredArticles.length === 0) {
			containerElement.style.display = 'none';
			return;
		}

		// Показываем компонент
		containerElement.style.display = 'block';

		// Заполняем слайдер
		filteredArticles.forEach(article => {
			const slideElement = this.createSlideElement(article);
			container.appendChild(slideElement);
		});

		// Активируем слайдер
		this.activateSlider();
	}

	/**
	 * Создание элемента слайдера
	 */
	createSlideElement(article) {
		const slide = document.createElement('a');
		// Используем сохраненный URL статьи, если он есть, иначе формируем из текущего раздела
		slide.href = article.URL || `${this.baseUrl}${article.CODE}/`;
		slide.className = 'native-slider__slide blog__viewed-article-link';

		slide.innerHTML = `
            <div class="blog__viewed-article-card">
                <h3 class="blog__viewed-article-title">${this.escapeHtml(article.NAME)}</h3>
            </div>
            <div class="blog__viewed-article-btn">
                <svg width="49" height="48" viewBox="0 0 49 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="0.34375" width="48" height="48" rx="15" fill="#5B61FF" />
                    <g clip-path="url(#clip0_3245_116715)">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M19.944 18.4772L19.9416 18.363C19.9607 17.8978 20.3246 17.4996 20.7768 17.4248L20.8916 17.413L29.9249 17.0418L29.9799 17.0412L30.0589 17.0461L30.1759 17.0668L30.2815 17.1006L30.3705 17.142L30.4814 17.2141L30.5591 17.2825L30.6038 17.3309L30.6697 17.4203L30.7188 17.5091L30.7581 17.6073L30.7832 17.7028L30.7985 17.8171L30.7999 17.9168L30.4286 26.9501C30.4079 27.454 29.9826 27.8793 29.4786 27.9001C29.0134 27.9192 28.6439 27.5868 28.606 27.1392L28.6036 27.0251L28.8831 20.1959L18.4146 30.6645C18.0436 31.0355 17.4658 31.0592 17.1241 30.7175C16.7824 30.3758 16.8061 29.7981 17.1771 29.4271L27.647 18.9572L20.8166 19.238C20.3514 19.2571 19.9818 18.9248 19.944 18.4772L19.9416 18.363L19.944 18.4772Z" fill="white" />
                    </g>
                </svg>
            </div>
        `;

		return slide;
	}

	/**
	 * Экранирование HTML
	 */
	escapeHtml(text) {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	/**
	 * Активация слайдера
	 */
	activateSlider() {
		// Проверяем, есть ли уже инициализированный слайдер
		const slider = document.querySelector('.js-native-slider');
		if (slider && slider.sliderInstance) {
			// Если слайдер уже инициализирован, обновляем пагинацию
			slider.sliderInstance.updatePagination();
		}
	}

	/**
 * Добавление обработчиков событий
 */
	addEventListeners() {
		// Обработка кликов по ссылкам на статьи (оптимизировано)
		document.addEventListener('click', (e) => {
			// Проверяем ссылки на статьи в любом разделе
			const articleLink = e.target.closest('a[href*="/our-blog/"], a[href*="/news/"], a[href*="/smi-o-nas/"]');
			if (articleLink && !articleLink.href.includes('#') && !articleLink.href.includes('mailto:') && !articleLink.href.includes('tel:')) {
				// Обновляем UI сразу, без задержек
				this.updateViewedArticlesUI();
			}
		});
	}

	/**
 * Очистка старых записей (старше 30 дней)
 */
	cleanupOldArticles() {
		const viewedArticles = this.getViewedArticles();
		if (viewedArticles.length === 0) return;

		const thirtyDaysAgo = new Date();
		thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);

		const filteredArticles = viewedArticles.filter(article => {
			const viewedDate = new Date(article.viewed_at);
			return viewedDate > thirtyDaysAgo;
		});

		if (filteredArticles.length !== viewedArticles.length) {
			this.saveToLocalStorage(filteredArticles);
		}
	}
}

// Инициализация сразу при загрузке скрипта
(function () {
	// Проверяем, что DOM готов
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initViewedArticles);
	} else {
		initViewedArticles();
	}

	function initViewedArticles() {
		// Создаем экземпляр менеджера
		window.viewedArticlesManager = new ViewedArticlesManager();

		// Очистка старых записей раз в день (асинхронно)
		setTimeout(() => {
			const lastCleanup = localStorage.getItem('viewed_articles_last_cleanup');
			const today = new Date().toDateString();

			if (lastCleanup !== today) {
				window.viewedArticlesManager.cleanupOldArticles();
				localStorage.setItem('viewed_articles_last_cleanup', today);
			}
		}, 100);
	}
})();
