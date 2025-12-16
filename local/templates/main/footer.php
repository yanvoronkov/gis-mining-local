<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();
?>
</main>


<footer class="site-footer footer">
    <div class="container">
        <!-- Верхняя часть футера: Лого, описание, контакты, карта (на десктопе) -->
        <div class="footer__top">
            <div class="footer__main-info">
                <a href="/" class="logo footer__logo">
                    <!-- ПУТЬ К ЛОГОТИПУ ИСПРАВЛЕН -->
                    <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/footer/logo_footer_dark.png"
                        alt="GIS Mining - Логотип компании" width="313" height="46">
                </a>
                <p class="footer__description">
                    Наша команда экспертов ответит на любые вопросы: от форматов инвестирования в майнинг до
                    юридических требований работы в
                    рамках реестра майнеров
                </p>
                <div class="footer__contacts-grid">
                    <a id="wa-link-footer" href="https://api.whatsapp.com/send/?phone=%2B79311116071" target="_blank"
                        rel="noopener noreferrer nofollow" class="footer__contact-item">
                        <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_5008_11409)">
                                <rect y="0.21875" width="24" height="24" fill="#18A53A" />
                                <path
                                    d="M15.6015 13.3381C15.5723 13.3241 14.4787 12.7856 14.2844 12.7157C14.2051 12.6872 14.1201 12.6594 14.0297 12.6594C13.882 12.6594 13.758 12.7329 13.6613 12.8775C13.5521 13.0399 13.2214 13.4264 13.1192 13.5419C13.1059 13.5572 13.0876 13.5754 13.0767 13.5754C13.0669 13.5754 12.8977 13.5057 12.8465 13.4834C11.6733 12.9738 10.7829 11.7484 10.6608 11.5417C10.6434 11.512 10.6426 11.4985 10.6425 11.4985C10.6468 11.4828 10.6862 11.4433 10.7066 11.4229C10.7661 11.3639 10.8307 11.2862 10.8931 11.2111C10.9227 11.1755 10.9523 11.1398 10.9814 11.1062C11.0719 11.0008 11.1123 10.919 11.159 10.8242L11.1835 10.775C11.2977 10.5482 11.2002 10.3568 11.1687 10.295C11.1428 10.2433 10.681 9.12882 10.632 9.01174C10.5139 8.7292 10.3579 8.59766 10.1411 8.59766C10.121 8.59766 10.1411 8.59766 10.0568 8.60121C9.95405 8.60555 9.39468 8.67919 9.14737 8.83509C8.8851 9.00044 8.44141 9.52751 8.44141 10.4544C8.44141 11.2887 8.97082 12.0764 9.19812 12.3759C9.20377 12.3835 9.21414 12.3988 9.22919 12.4208C10.0997 13.6921 11.1848 14.6342 12.2849 15.0737C13.344 15.4967 13.8455 15.5456 14.1306 15.5456H14.1306C14.2504 15.5456 14.3463 15.5362 14.4309 15.5279L14.4846 15.5228C14.8505 15.4904 15.6545 15.0737 15.8374 14.5655C15.9814 14.1652 16.0194 13.7279 15.9236 13.5691C15.8579 13.4612 15.7448 13.4069 15.6015 13.3381Z"
                                    fill="white" />
                                <path
                                    d="M12.1335 4.71875C8.07085 4.71875 4.76562 7.99914 4.76562 12.0313C4.76562 13.3354 5.11463 14.612 5.77578 15.7292L4.51031 19.4621C4.48674 19.5317 4.50427 19.6087 4.55575 19.6611C4.59292 19.699 4.64332 19.7195 4.6948 19.7195C4.71453 19.7195 4.7344 19.7165 4.75378 19.7103L8.64618 18.4735C9.71133 19.0426 10.9152 19.343 12.1336 19.343C16.1958 19.343 19.5007 16.063 19.5007 12.0313C19.5007 7.99914 16.1958 4.71875 12.1335 4.71875ZM12.1335 17.8198C10.9871 17.8198 9.87668 17.4887 8.92219 16.8624C8.89009 16.8413 8.85283 16.8305 8.81533 16.8305C8.79551 16.8305 8.77564 16.8335 8.7563 16.8396L6.80645 17.4594L7.4359 15.6024C7.45626 15.5423 7.44608 15.476 7.40857 15.4248C6.68172 14.4316 6.2975 13.2582 6.2975 12.0313C6.2975 8.83909 8.91552 6.24201 12.1335 6.24201C15.351 6.24201 17.9688 8.83909 17.9688 12.0313C17.9688 15.2231 15.3511 17.8198 12.1335 17.8198Z"
                                    fill="white" />
                            </g>
                            <defs>
                                <clipPath id="clip0_5008_11410">
                                    <rect width="24" height="24" rx="5.25" transform="matrix(1 0 0 -1 0 24.2188)"
                                        fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                        <span class="footer__contact-text">Whatsapp</span>
                    </a>
                    <a id="tg-link-footer" href="https://t.me/gismining_official" target="_blank"
                        rel="noopener noreferrer nofollow" class="footer__contact-item">
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.75" y="0.21875" width="24" height="24" rx="5.25" fill="#006FFF" />
                            <path
                                d="M18.2212 4.85117C18.2212 4.85117 19.6088 4.20189 19.4932 5.77873C19.4546 6.42802 19.1077 8.70052 18.8379 11.1585L17.9128 18.4398C17.9128 18.4398 17.8358 19.5065 17.1419 19.692C16.4481 19.8775 15.4074 19.0428 15.2147 18.8572C15.0605 18.7181 12.3239 16.6311 11.3602 15.6108C11.0904 15.3325 10.782 14.776 11.3988 14.1267L15.446 9.48896C15.9085 8.93242 16.371 7.63385 14.4438 9.21069L9.04754 13.6166C9.04754 13.6166 8.43082 14.0803 7.2745 13.6629L4.76908 12.7354C4.76908 12.7354 3.844 12.0397 5.42434 11.344C9.27883 9.16428 14.0198 6.93815 18.2212 4.85117Z"
                                fill="white" />
                        </svg>
                        <span class="footer__contact-text">Telegram</span>
                    </a>
                    <a href="tel:+74951919874" class="footer__contact-item footer__contact-item--full-width">
                        <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect y="0.21875" width="24" height="24" rx="5.25" fill="#3238E0" />
                            <path
                                d="M19.4925 16.0457C19.3597 17.0601 18.863 17.9916 18.0948 18.6672C17.3266 19.3428 16.3393 19.7165 15.3163 19.7187C9.3523 19.7187 4.5 14.8665 4.5 8.90249C4.50226 7.87949 4.87592 6.89212 5.55152 6.12395C6.22713 5.35578 7.15869 4.85909 8.17302 4.72621C8.4316 4.69754 8.69258 4.75198 8.91813 4.88163C9.14367 5.01129 9.32206 5.20942 9.42741 5.44729L10.9372 8.97009C11.0155 9.15295 11.0469 9.35247 11.0285 9.55054C11.0102 9.74861 10.9426 9.93895 10.832 10.1043L9.58514 12.0122C10.1498 13.158 11.0801 14.083 12.2291 14.6411L14.1145 13.3867C14.2796 13.2754 14.4704 13.208 14.6689 13.1909C14.8674 13.1738 15.0669 13.2076 15.2487 13.2891L18.7715 14.7913C19.0093 14.8967 19.2075 15.0751 19.3371 15.3006C19.4668 15.5262 19.5212 15.7872 19.4925 16.0457Z"
                                fill="white" />
                        </svg>
                        <span class="footer__contact-text">+7 (495) 191-98-74</span>
                    </a>
                    <a href="mailto:info@gis-corp.ru" class="footer__contact-item footer__contact-item--full-width">
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="0.75" y="0.21875" width="24" height="24" rx="5.25" fill="#3238E0" />
                            <path
                                d="M6.30224 18.2188C5.98744 18.2188 5.71644 18.1052 5.48924 17.8779C5.26204 17.6508 5.14844 17.3797 5.14844 17.0649V7.37255C5.14844 7.05775 5.26204 6.78675 5.48924 6.55955C5.71644 6.33235 5.98744 6.21875 6.30224 6.21875H19.1946C19.5094 6.21875 19.7804 6.33235 20.0076 6.55955C20.2348 6.78675 20.3484 7.05775 20.3484 7.37255V17.0649C20.3484 17.3797 20.2348 17.6508 20.0076 17.8779C19.7804 18.1052 19.5094 18.2188 19.1946 18.2188H6.30224ZM12.7484 12.2279C12.81 12.2279 12.8654 12.2179 12.9146 12.1979C12.9638 12.1779 13.0151 12.1546 13.0684 12.1279L19.2406 8.05875C19.3022 8.01875 19.3446 7.96668 19.3676 7.90255C19.3907 7.83842 19.3946 7.77148 19.3792 7.70175C19.369 7.58482 19.3051 7.49302 19.1876 7.42635C19.0702 7.35968 18.9546 7.36435 18.8408 7.44035L12.7484 11.3864L6.66844 7.44035C6.55457 7.36435 6.43817 7.35508 6.31924 7.41255C6.2003 7.47002 6.1331 7.55722 6.11764 7.67415C6.1023 7.74388 6.10824 7.81542 6.13544 7.88875C6.16264 7.96208 6.2029 8.01875 6.25624 8.05875L12.436 12.1279C12.4843 12.1546 12.533 12.1779 12.5822 12.1979C12.6314 12.2179 12.6868 12.2279 12.7484 12.2279Z"
                                fill="white" />
                        </svg>
                        <span class="footer__contact-text">info@gis-corp.ru</span>
                    </a>
                </div>
            </div>

            <div class="footer__map-block">

                <div id="footerMap" class="footer__map-embed">

                    <div style="position:relative;overflow:hidden;">
                        <a href="https://yandex.by/maps/org/gis_mayning/105753191121/?utm_medium=mapframe&utm_source=maps"
                            style="color:#eee;font-size:12px;position:absolute;top:0px;" rel="nofollow">ГИС майнинг</a>
                        <a href="https://yandex.by/maps/213/moscow/category/it_company/184106174/?utm_medium=mapframe&utm_source=maps"
                            style="color:#eee;font-size:12px;position:absolute;top:14px;" rel="nofollow">IT-компания в
                            Москве</a>
                        <iframe
                            src="https://yandex.by/map-widget/v1/?ll=37.625909%2C55.703992&mode=search&no-distribution=1&oid=105753191121&ol=biz&sll=52.674253%2C58.140801&source=wizbiz_new_map_single&sspn=0.123253%2C0.037083&text=117105%2C%20%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0%2C%20%D0%92%D0%B0%D1%80%D1%88%D0%B0%D0%B2%D1%81%D0%BA%D0%BE%D0%B5%20%D1%88%D0%BE%D1%81%D1%81%D0%B5%2C%201%2C%20%D1%81%D1%82%D1%80.%201-2%20W-Plaza&utm_source=share&z=17"
                            width="560" height="400" allowfullscreen style="position:relative;" loading="lazy">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
        <h2 class="footer__slogan">Цифровые ресурсы<br> для Вас !</h2>

        <!-- Нижняя часть футера: Копирайт, юридическая информация, кнопка "Наверх" -->
        <div class="footer__bottom">
            <?php $APPLICATION->IncludeComponent(
                "bitrix:menu",
                "footer_menu", // Указываем название нашего нового шаблона
                array(
                    "ALLOW_MULTI_SELECT" => "N",
                    "CHILD_MENU_TYPE" => "left",
                    "DELAY" => "N",
                    "MAX_LEVEL" => "1", // Меню одноуровневое
                    "MENU_CACHE_GET_VARS" => array(""),
                    "MENU_CACHE_TIME" => "3600",
                    "MENU_CACHE_TYPE" => "A",
                    "MENU_CACHE_USE_GROUPS" => "Y",
                    "ROOT_MENU_TYPE" => "bottom", // <-- Ключевой параметр: используем данные из .bottom.menu.php
                    "USE_EXT" => "N" // <-- Нам не нужно расширять это меню, поэтому "N" 5555
                )
            ); ?>

            <div class="footer_legal-items">
                <!-- ГОД СДЕЛАН ДИНАМИЧЕСКИМ -->
                <p class="footer__copyright">© <?= date('Y') ?> GIS Mining</p>
                <p class="footer__legal-info">Не является публичной офертой</p>
                <a href="/policy-confidenciales/" class="footer__privacy-link">Политика конфиденциальности</a>
            </div>
            <button type="button" class="footer__scroll-top" aria-label="Наверх страницы">
                <svg width="42" height="43" viewBox="0 0 42 43" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="21" cy="21.375" rx="21" ry="21" transform="rotate(-90 21 21.375)"
                        fill="url(#paint0_linear_5008_11621)" />
                    <path
                        d="M15.6572 19.8389L20.7777 14.7184M20.7777 14.7184L25.8981 19.8389M20.7777 14.7184V24.9593V28.0316"
                        stroke="white" stroke-width="0.75" />
                </svg>
            </button>
        </div>

        <!-- PopUP basket -->
        <div class="page-asics__popup-form popup-form-wrapper js-cart-modal" id="cartPopupFormWrapper"
            style="display: none;">

            <!-- ВАЖНО: У формы теперь есть класс js-ajax-form, чтобы ее "подхватил" form-actions.js -->
            <form class="popup-form js-ajax-form" id="cartForm" data-metric-goal="send-cart-lead">

                <button type="button" class="popup-form__close-btn menu-close" id="closeCartPopupFormBtn"
                    aria-label="Закрыть">
                    <span>
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 18 18" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M17 1L1 17M1 1L17 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"></path>
                        </svg>
                    </span>
                </button>

                <p class="popup-form__text">Заказ ASIC-майнеров</p>

                <div class="cart-items-container js-cart-items-container">
                    <p>Загрузка товаров...</p>
                </div>

                <div class="cart-summary js-cart-summary"></div>

                <p class="popup-form__cta">Заполните форму, чтобы сделать заказ. Мы перезвоним вам в ближайшее время</p>

                <label for="cart_client_name_cart">Ваше имя*:</label>
                <input type="text" name="client_name" id="cart_client_name_cart" placeholder="Имя"
                    class="contact-form__input form-input" required aria-label="Имя">

                <label for="cart_client_phone_cart">Телефон*:</label>
                <input type="tel" name="client_phone" id="cart_client_phone_cart" placeholder="Телефон*"
                    class="contact-form__input form-input js-phone-mask" required aria-label="Номер телефона">

                <label for="cart_client_email">Email:</label>
                <input type="email" name="client_email" id="cart_client_email"
                    placeholder="your@email.com (необязательно)" class="contact-form__input form-input"
                    aria-label="Электронная почта">

                <input type="hidden" name="utm_source">
                <input type="hidden" name="utm_medium">
                <input type="hidden" name="utm_campaign">
                <input type="hidden" name="utm_content">
                <input type="hidden" name="utm_term">
                <input type="hidden" name="source_id" value="25">
                <input type="hidden" name="page_url" value="">
                <!-- Название формы для CRM -->
                <input type="hidden" name="form_name" value="Заказ из корзины">
                <!-- Сюда cart.js будет добавлять JSON с товарами -->
                <input type="hidden" name="cart_items" value="">

                <div class="form-group form-check mb-3">
                    <input type="checkbox" id="privacy-policy-footer" name="privacy-policy" class="form-check-input"
                        required>
                    <label for="privacy-policy-footer" class="form-check-label">Согласен(а) с <a
                            href="/policy-confidenciales/" target="_blank"><u>политикой
                                конфиденциальности</u></a></label>
                </div>

                <div class="cart-form__buttons">
                    <button type="submit" class="btn btn-primary">Отправить заказ</button>
                    <button type="button" class="btn btn-secondary js-clear-cart">Очистить корзину</button>
                </div>

                <p class="form-error-message" style="color: red; display: none;"></p>
            </form>
        </div>

        <!-- PopUP success -->
        <div class="modal-overlay" id="cartSuccessModalOverlay">
            <div class="modal" id="cartSuccessModal">
                <button class="modal__close-btn" id="closeCartSuccessModalBtn" aria-label="Закрыть">
                    <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.875 22.2988L22.875 2.71741" stroke="#131315" stroke-linecap="round" />
                        <path d="M2.875 2.29883L22.875 21.8802" stroke="#131315" stroke-linecap="round" />
                    </svg>

                </button>
                <div class="modal__icon">
                    <!-- Можно добавить иконку галочки SVG или Font Awesome -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="50px" height="50px">
                        <path fill="#10B981"
                            d="M25,2C12.318,2,2,12.318,2,25s10.318,23,23,23s23-10.318,23-23S37.682,2,25,2z M37.12,20.432 l-13.068,13.068c-0.196,0.196-0.454,0.294-0.712,0.294s-0.516-0.098-0.712-0.294l-6.918-6.918c-0.392-0.392-0.392-1.028,0-1.42s1.028-0.392,1.42,0l6.206,6.206l12.356-12.356c0.392-0.392,1.028-0.392,1.42,0S37.512,20.04,37.12,20.432z" />
                    </svg>
                </div>
                <h3 class="modal__title">Заявка принята!</h3>
                <p class="modal__text">Благодарим! Мы свяжемся с вами в ближайшее время.</p>
            </div>
        </div>

        <!-- POPUP SUCCESS -->
        <div class="modal-overlay" id="mainSuccessModalOverlay">
            <div class="modal" id="mainSuccessModal">
                <button class="modal__close-btn" id="closeMainSuccessModalBtn" aria-label="Закрыть">
                    <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.875 22.2988L22.875 2.71741" stroke="#131315" stroke-linecap="round" />
                        <path d="M2.875 2.29883L22.875 21.8802" stroke="#131315" stroke-linecap="round" />
                    </svg>

                </button>
                <div class="modal__icon">
                    <!-- Можно добавить иконку галочки SVG или Font Awesome -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="50px" height="50px">
                        <path fill="#10B981"
                            d="M25,2C12.318,2,2,12.318,2,25s10.318,23,23,23s23-10.318,23-23S37.682,2,25,2z M37.12,20.432 l-13.068,13.068c-0.196,0.196-0.454,0.294-0.712,0.294s-0.516-0.098-0.712-0.294l-6.918-6.918c-0.392-0.392-0.392-1.028,0-1.42s1.028-0.392,1.42,0l6.206,6.206l12.356-12.356c0.392-0.392,1.028-0.392,1.42,0S37.512,20.04,37.12,20.432z" />
                    </svg>
                </div>
                <h3 class="modal__title">Заявка принята!</h3>
                <p class="modal__text">Благодарим! Мы свяжемся с вами в ближайшее время.</p>
            </div>
        </div>


        <!-- PopUp Рассрочка -->
        <div class="page-business__popup-form popup-form-wrapper js-cart-modal" id="rassrochkaPopupFormWrapper"
            style="display: none;">

            <!-- ВАЖНО: У формы теперь есть класс js-ajax-form, чтобы ее "подхватил" form-actions.js -->
            <form class="popup-form js-ajax-form" id="rassrochka_form" data-metric-goal="send-rassrochka-lead">

                <button type="button" class="popup-form__close-btn menu-close" id="closeRassrochkaPopupFormBtn"
                    aria-label="Закрыть">
                    <span>
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 18 18" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M17 1L1 17M1 1L17 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"></path>
                        </svg>
                    </span>
                </button>

                <p class="popup-form__text">Начни майнить сейчас, а плати потом</p>

                <p class="popup-form__cta">Без первоначального взноса на 3, 6 или 12 месяцев. Заполните форму и получите
                    подробные условия рассрочки</p>

                <label for="cart_client_name_rassrochka">Ваше имя*:</label>
                <input type="text" name="client_name" id="cart_client_name_rassrochka" placeholder="Имя"
                    class="contact-form__input form-input" required aria-label="Имя">

                <label for="cart_client_phone_rassrochka">Телефон*:</label>
                <input type="tel" name="client_phone" id="cart_client_phone_rassrochka" placeholder="Телефон*"
                    class="contact-form__input form-input js-phone-mask" required aria-label="Номер телефона">


                <input type="hidden" name="utm_source">
                <input type="hidden" name="utm_medium">
                <input type="hidden" name="utm_campaign">
                <input type="hidden" name="utm_content">
                <input type="hidden" name="utm_term">
                <input type="hidden" name="source_id" value="28">
                <input type="hidden" name="page_url" value="">
                <!-- Название формы для CRM -->
                <input type="hidden" name="form_name" value="Рассрочка">

                <div class="form-group form-check mb-3">
                    <input type="checkbox" id="privacy-policy" name="privacy-policy" class="form-check-input" required>
                    <label for="privacy-policy" class="form-check-label">Согласен(а) с <a href="/policy-confidenciales/"
                            target="_blank"><u>политикой конфиденциальности</u></a></label>
                </div>
                <button type="submit" class="btn btn-primary">Получить условия</button>

                <p class="form-error-message" style="color: red; display: none;"></p>
            </form>
        </div>


        <!-- Соцсети, пока скрытые -->
        <div class="social-links seo-hidden-links" id="siteSocialLinks">
            <!-- Начально скрытый блок -->
            <p class="social-links__title seo-hidden-links__text-content">Мы в социальных сетях</p>
            <!-- Скрытый заголовок для доступности -->
            <ul class="social-links__list">
                <li class="social-links__item">
                    <a href="https://t.me/gismining" target="_blank" rel="noopener noreferrer nofollow"
                        class="social-links__link social-links__link--telegram seo-hidden-links__text-content"
                        aria-label="Наш Telegram канал">
                        <!-- Здесь может быть SVG иконка или <img> -->
                        <span class="social-links__icon"><!-- SVG или Font Icon --></span>
                        <span class="social-links__text">ТГ канал</span>
                        <!-- Текст можно скрыть CSS, если нужны только иконки -->
                    </a>
                </li>
                <li class="social-links__item">
                    <a href="https://vk.com/gis_mining?from=groups" target="_blank" rel="noopener noreferrer nofollow"
                        class="social-links__link social-links__link--vk seo-hidden-links__text-content"
                        aria-label="Мы ВКонтакте">
                        <span class="social-links__icon"><!-- SVG или Font Icon --></span>
                        <span class="social-links__text">Вконтакте</span>
                    </a>
                </li>
                <li class="social-links__item">
                    <a href="https://www.instagram.com/gismining?igsh=a2loOTI4bG1oMG92" target="_blank"
                        rel="noopener noreferrer nofollow"
                        class="social-links__link social-links__link--instagram seo-hidden-links__text-content"
                        aria-label="Наш Instagram">
                        <span class="social-links__icon"><!-- SVG или Font Icon --></span>
                        <span class="social-links__text">Instagram</span>
                    </a>
                </li>
                <li class="social-links__item">
                    <a href="https://www.facebook.com/Gismining" target="_blank" rel="noopener noreferrer nofollow"
                        class="social-links__link social-links__link--facebook seo-hidden-links__text-content"
                        aria-label="Наш Facebook">
                        <span class="social-links__icon"><!-- SVG или Font Icon --></span>
                        <span class="social-links__text">Facebook</span>
                    </a>
                </li>
                <li class="social-links__item">
                    <a href="https://vc.ru/id4624566" target="_blank" rel="noopener noreferrer nofollow"
                        class="social-links__link social-links__link--vcru seo-hidden-links__text-content"
                        aria-label="Мы на VC.ru">
                        <span class="social-links__icon"><!-- SVG или Font Icon --></span>
                        <span class="social-links__text">VC.ru</span>
                    </a>
                </li>
                <li class="social-links__item">
                    <a href="https://ru.tradingview.com/u/GIS-Mining/#settings-profile" target="_blank"
                        rel="noopener noreferrer nofollow"
                        class="social-links__link social-links__link--tradingview seo-hidden-links__text-content"
                        aria-label="Наш профиль на Trading View">
                        <span class="social-links__icon"><!-- SVG или Font Icon --></span>
                        <span class="social-links__text">Trading View</span>
                    </a>
                </li>
                <li class="social-links__item">
                    <a href="https://dzen.ru/user/wz4h9o1t6gyei9xirsrwh20ctri" target="_blank"
                        rel="noopener noreferrer nofollow"
                        class="social-links__link social-links__link--dzen seo-hidden-links__text-content"
                        aria-label="Наш канал в Яндекс.Дзен">
                        <span class="social-links__icon"><!-- SVG или Font Icon --></span>
                        <span class="social-links__text">Я.Дзен</span>
                    </a>
                </li>
                <li class="social-links__item">
                    <a href="https://www.threads.com/@gismining" target="_blank" rel="noopener noreferrer nofollow"
                        class="social-links__link social-links__link--threads seo-hidden-links__text-content"
                        aria-label="Наш профиль в Threads">
                        <span class="social-links__icon"><!-- SVG или Font Icon --></span>
                        <span class="social-links__text">Threads</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Cookie Banner -->
        <div id="cookie-banner" style="
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    width: calc(100% - 20px);
    max-width: 1200px;
    background: #fff;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    padding: 20px 24px;
    display: none;
    z-index: 10000;
    border-radius: 12px;
    box-sizing: border-box;
">
            <div
                style="display: flex; align-items: flex-start; justify-content: space-between; gap: 15px; flex-wrap: wrap;">
                <div style="
            flex: 1;
            min-width: 0;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
        ">
                    Мы используем <a href="/policy-confidenciales/" target="_blank"
                        style="color:#5b61ff;text-decoration:underline;">файлы cookie</a>,
                    чтобы обеспечивать правильную работу сайта и анализировать сетевой трафик.<br>Продолжая работу на
                    сайте, вы соглашаетесь с
                    <a href="/policy-confidenciales/" target="_blank"
                        style="color:#5b61ff;text-decoration:underline;">политикой конфиденциальности</a>.
                </div>
                <button id="cookie-accept" style="
            background: #5b61ff;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 6px 10px 8px;
            font-size: 14px;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.2s ease;
        ">Согласен</button>
            </div>
        </div>

        <script>
            (function () {
                if (!sessionStorage.getItem('cookieBannerClosed')) {
                    var banner = document.getElementById('cookie-banner');
                    banner.style.display = 'block';

                    var acceptBtn = document.getElementById('cookie-accept');
                    acceptBtn.addEventListener('click', function () {
                        banner.style.transition = 'transform 0.4s ease, opacity 0.4s ease';
                        banner.style.transform = 'translate(-50%, 100px)'; // плавный уход вниз
                        banner.style.opacity = '0';
                        setTimeout(function () {
                            banner.style.display = 'none';
                        }, 400);

                        sessionStorage.setItem('cookieBannerClosed', 'true');
                    });
                }
            })();
        </script>


        <!-- Global Popup (универсальный) -->
        <div class="global-popup-overlay" id="globalPopupOverlay">
            <div class="global-popup">
                <button class="global-popup__close-btn" id="closeGlobalPopupBtn" aria-label="Закрыть">&times;</button>
                <div class="global-popup__icon" id="globalPopupIcon"></div>
                <div class="global-popup__title" id="globalPopupTitle" style="display: none;"></div>
                <p class="global-popup__text" id="globalPopupText"></p>
            </div>
        </div>




        <!-- Парсинг UTM и запись в куки -->
        <script>
            (function () {
                'use strict';

                // Функция для получения значения параметра из URL
                function getParameterByName(name, url) {
                    if (!url) url = window.location.href;
                    name = name.replace(/[\[\]]/g, '\\$&');
                    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                        results = regex.exec(url);
                    if (!results) return null;
                    if (!results[2]) return '';
                    return decodeURIComponent(results[2].replace(/\+/g, ' '));
                }

                // Функция для установки cookie
                function setCookie(name, value, days) {
                    var expires = "";
                    if (days) {
                        var date = new Date();
                        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                        expires = "; expires=" + date.toUTCString();
                    }
                    document.cookie = name + "=" + (value || "") + expires + "; path=/";
                }

                // Функция для получения cookie
                function getCookie(name) {
                    var nameEQ = name + "=";
                    var ca = document.cookie.split(';');
                    for (var i = 0; i < ca.length; i++) {
                        var c = ca[i];
                        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
                    }
                    return null;
                }

                // Проверяем наличие UTM-меток в URL
                var utm_source = getParameterByName('utm_source');
                var utm_medium = getParameterByName('utm_medium');
                var utm_campaign = getParameterByName('utm_campaign');
                var utm_content = getParameterByName('utm_content');
                var utm_term = getParameterByName('utm_term');

                // Если есть UTM-метки, сохраняем их в cookies (на 30 дней)
                if (utm_source) setCookie('utm_source', utm_source, 30);
                if (utm_medium) setCookie('utm_medium', utm_medium, 30);
                if (utm_campaign) setCookie('utm_campaign', utm_campaign, 30);
                if (utm_content) setCookie('utm_content', utm_content, 30);
                if (utm_term) setCookie('utm_term', utm_term, 30);

                // Функция для заполнения UTM-полей в формах
                function fillUtmFields() {
                    var utmFields = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];

                    utmFields.forEach(function (fieldName) {
                        var fieldValue = getCookie(fieldName) || '';
                        if (fieldValue) {
                            // Ищем все поля с данным именем
                            var fields = document.querySelectorAll('input[name="' + fieldName + '"]');
                            fields.forEach(function (field) {
                                // Заполняем только пустые поля или обновляем значение
                                if (!field.value || field.value !== fieldValue) {
                                    field.value = fieldValue;
                                }
                            });
                        }
                    });
                }

                // Выполняем при загрузке DOM
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', fillUtmFields);
                } else {
                    fillUtmFields();
                }

                // Также заполняем поля при сабмите формы
                document.addEventListener('submit', function (e) {
                    setTimeout(fillUtmFields, 0);
                });
            })();
        </script>
        <!-- Конец -->

        <!-- Цель в чат Битрикс -->
        <script>
            window.addEventListener('onBitrixLiveChat', function (event) {
                var widget = event.detail.widget;

                // Обработка событий 
                widget.subscribe({
                    type: BX.LiveChatWidget.SubscriptionType.userMessage,
                    callback: function (data) {

                        // любая команда

                        if (typeof (dataLayer) == 'undefined') {
                            dataLayer = [];
                        }
                        dataLayer.push({
                            "ecommerce": {
                                "purchase": {
                                    "actionField": {
                                        "id": "chat-message",
                                        "goal_id": "464131339"
                                    },
                                    "products": [{}]
                                }
                            }
                        });
                    }
                });
            });
        </script>
        <!-- Конец цель чат Битрикс -->


        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const metrikaId = 102682922;

                ym(metrikaId, 'getClientID', function (clientID) {
                    if (!clientID) return;

                    // WhatsApp — проставляем всем элементам с id начинающимся на "wa-link"
                    document.querySelectorAll('[id^="wa-link"]').forEach(function (waLink) {
                        const base = "https://api.whatsapp.com/send/?phone=%2B79311116071&text=";
                        const msg = encodeURIComponent(
                            "Здравствуйте! Мой промокод: " + clientID + "\n" +
                            "У меня вопрос по оборудованию для майнинга\n" +
                            "Сможете помочь?"
                        );
                        waLink.href = base + msg;
                    });

                    // Telegram — теперь это БОТ
                    document.querySelectorAll('[id^="tg-link"]').forEach(function (tgLink) {
                        const base = "https://t.me/gismining_chat_bot?start=cid_";
                        tgLink.href = base + encodeURIComponent(clientID);
                    });

                });
            });
        </script>




        <link rel="stylesheet" href="https://cdn.envybox.io/widget/cbk.css">
        <script src="https://cdn.envybox.io/widget/cbk.js?wcb_code=acddf2b9c7f18f744cd8e77ebe572cb0" async></script>





    </div>
</footer>

<?php
// Выводим контент, добавленный через AddViewContent('before_body_close')
$APPLICATION->ShowViewContent('before_body_close');
?>


<?php
// Подключаем стили и меню
$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . '/include/mobile_menu.css');
include $_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . '/include/mobile_menu.php';
?>

<?php
// Выводим централизованные JSON-LD схемы (Organization, WebSite + динамические)
echo \Local\Seo\SeoManager::getJsonLd();

// Выводим схемы компонентов (BreadcrumbList, Product, и т.д.)
$APPLICATION->ShowViewContent('json_ld_schemas');
?>

<!-- Organization и WebSite schemas теперь управляются через renderJsonLdSchemas() в init.php -->

<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Какая стоимость размещения в дата-центре GIS Mining?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Тариф на электроэнергию начинается от 4,19 ₽/кВт⋅ч на площадках категории А (АЭС). Итоговая стоимость зависит от мощности, модели оборудования и условий договора. Уточните тариф у отдела продаж."
                }
            },
            {
                "@type": "Question",
                "name": "Как быстро запускается оборудование после поставки?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "При наличии свободных мощностей стандартный срок ввода — 3–7 рабочих дней с момента поставки и подписания документов. Включает приемку, маркировку и подключение в стойках."
                }
            },
            {
                "@type": "Question",
                "name": "Есть ли гарантия и сервис?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "На новое оборудование действует гарантия производителя 6–12 месяцев. На площадках GIS Mining доступен бесплатный ремонт в рамках сервисной программы, условия уточняйте в договоре."
                }
            },
            {
                "@type": "Question",
                "name": "Можно ли разместить оборудование, купленное не у вас?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Да. Мы принимаем ASIC, приобретенные у сторонних поставщиков, при условии прохождения входной диагностики и соответствия требованиям по электропитанию и охлаждению."
                }
            },
            {
                "@type": "Question",
                "name": "Какой аптайм обеспечивается?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Целевой уровень доступности — 97–99% в год с учетом регламентных работ и форс-мажоров. Фактические показатели фиксируются в SLA и отчётности мониторинга."
                }
            },
            {
                "@type": "Question",
                "name": "Работаете ли вы официально в РФ?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Да. Компания включена в официальный реестр операторов майнинговой инфраструктуры и реализует проекты в соответствии с действующим законодательством РФ."
                }
            }
        ]
    }
</script>


</body>

</html>