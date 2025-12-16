<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>

<!-- Секция "Обратная связь" -->
<section class="feedback-section container section-padding">

    <div class="feedback-section__contact-form-wrapper">
        <h2 class="feedback-section__title section-title">Хотите задать вопрос?
        </h2>
        <!-- Контактная форма 2 -->
        <form id="contactForm2" class="hero__form contact-form js-ajax-form">
            <p class="contact-form__description">
                Оставьте заявку, и наш менеджер проконсультирует вас
            </p>
            <div class="input-group">
                <input type="tel" name="client_phone" class="js-phone-mask form-input" placeholder="Телефон*"
                       required="">
                <input type="hidden" name="form_name" value="Задать вопрос">
                <!-- Универсальные UTM поля -->
                <input type="hidden" name="utm_source">
                <input type="hidden" name="utm_medium">
                <input type="hidden" name="utm_campaign">
                <input type="hidden" name="utm_content">
                <input type="hidden" name="utm_term">
                <input type="hidden" name="source_id" value="26">
                <input type="hidden" name="page_url" value="">
                <button type="submit" class="btn btn-primary contact-form-submit-btn">Получить
                    консультацию</button>
            </div>
            <div class="form-group form-check mb-3">
              <input type="checkbox" id="privacy-policy-feedback" name="privacy-policy" class="form-check-input" required>
              <label for="privacy-policy-feedback" class="form-check-label">Согласен(а) с <a href="/policy-confidenciales/" target="_blank"><u>политикой конфиденциальности</u></a></label>
            </div>
            <!-- Сообщения об ошибке -->
            <p class="form-error-message"></p>
        </form>
    </div>

    <div class="feedback-section__telegram-channel item-block">
        <div class="feedback-section__telegram-content">
            <div class="feedback-section__telegram-content-title section-title">Наш <br> Telegram</div>
            <p class="feedback-section__telegram-content-description">
                Подписывайтесь на наш Telegram-канал, чтобы оперативно узнавать о новостях,
                акциях и
                получать лучшие предложения от GIS
                Mining
            </p>
            <a href="https://t.me/gismining" target="_blank" rel="noopener noreferrer nofollow"
               class="feedback-section__telegram-content-link btn btn-secondary">
                Подписаться на канал
            </a>
        </div>
        <div class="feedback-section__qr-code-wrapper">
            <picture>
                <source media="(min-width: 1200px)"
                        srcset="<?= SITE_TEMPLATE_PATH ?>/assets/img/components/telegram-channel-block_qr_desktop.webp" type="image/webp">

                <img class="feedback-section__qr-code"
                     src="<?= SITE_TEMPLATE_PATH ?>/assets/img/components/telegram-channel-block_qr_desktop.png"
                     alt="QR-код для подписки на Telegram канал GIS Mining"
                     loading="lazy"
                     width="381"
                     height="355">
            </picture>
        </div>
    </div>
</section>