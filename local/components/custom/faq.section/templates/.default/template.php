<?php
/**
 * Шаблон FAQ секции для каталога
 * Шаблон: .default (используется для ASIC, Videocard и калькулятора)
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @var CBitrixComponentTemplate $this */

$this->setFrameMode(true);
?>

<!-- Секция "Часто задаваемые вопросы" (FAQ) -->
<section class="faq-section section-padding">
    <div class="container">
        <h2>Часто задаваемые вопросы</h2>
        <div class="faq-list">
            <details class="faq-item">
                <summary class="faq-item__question">Какие способы оплаты у вас есть?</summary>
                <div class="faq-item__answer">
                    <p>Наличный и безналичный расчет, оплата по договору.</p>
                </div>
            </details>
            <details class="faq-item">
                <summary class="faq-item__question">Какой срок поставки?</summary>
                <div class="faq-item__answer">
                    <p>От 1 дня (в зависимости от наличия интересующего объема).</p>
                </div>
            </details>
            <details class="faq-item">
                <summary class="faq-item__question">Есть ли у вас мастерские? Какие сроки ремонта оборудования на
                    хостинге?</summary>
                <div class="faq-item__answer">
                    <p>Наши дата-центры оборудованы собственными мастерскими с квалифицированной командой специалистов.
                        При невозможности оперативного устранения поломки, в рамках гарантийного срока предоставляем
                        подменное устройство на весь срок ремонта.</p>
                </div>
            </details>
            <details class="faq-item is-hidden-initially">
                <summary class="faq-item__question">Можно купить оборудование в рассрочку? Какие условия?</summary>
                <div class="faq-item__answer">
                    <p>Условия рассрочки и лизинга обсуждаются индивидуально. Для получения коммерческого предложения
                        свяжитесь с нашими менеджерами любым удобным способом.</p>
                </div>
            </details>
            <details class="faq-item is-hidden-initially">
                <summary class="faq-item__question">Где располагается дата-центр?</summary>
                <div class="faq-item__answer">
                    <p>Хостинг запущен на территории Калининской АЭС в Тверской области. ЦОД «Калининский» располагается
                        всего в 380 км. от Москвы, что упрощает логистику и позволяет организовывать доступные экскурсии
                        для действующих и потенциальных клиентов.</p>
                </div>
            </details>
            <details class="faq-item is-hidden-initially">
                <summary class="faq-item__question">Вы состоите в реестре майнеров РФ?</summary>
                <div class="faq-item__answer">
                    <p>Да, компания GIS Mining является участником реестра в строгом соответствии с действующим
                        законодательством России.</p>
                </div>
            </details>
            <div class="faq-actions">
                <button type="button" class="btn faq-actions__load-more load-more-button"
                    data-load-more-target=".faq-section .faq-item.is-hidden-initially" data-load-more-count="6"
                    id="faqLoadMoreBtn2">Показать ещё
                    <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 1.4043L6 6.4043L1 1.4043" stroke="#131315" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</section>