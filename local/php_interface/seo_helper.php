<?php
/**
 * Хелпер для установки SEO-мета тегов каталога
 */

use Bitrix\Main\Context;

if (!function_exists('setCatalogSeo')) {
    /**
     * Устанавливает SEO-теги страницы каталога
     *
     * Параметры:
     * - title: обязательный, текст для <title> (PageProperty "TITLE")
     * - h1: заголовок страницы (SetTitle), если не передан — берется title
     * - description: Meta Description
     * - robots: значение мета robots (по умолчанию "index, follow")
     * - og_image: URL изображения для OpenGraph (по умолчанию картинка из шаблона)
     * - og_type: тип OpenGraph (по умолчанию "website")
     * - twitter_card: тип Twitter Card (по умолчанию "summary_large_image")
     */
    function setCatalogSeo(array $params): void
    {
        global $APPLICATION;

        $title = $params['title'] ?? '';
        if ($title === '') {
            return;
        }

        $h1 = $params['h1'] ?? $title;
        $metaTitle = $params['meta_title'] ?? $title;
        $description = $params['description'] ?? '';
        $robots = $params['robots'] ?? 'index, follow';
        $ogType = $params['og_type'] ?? 'website';
        $twitterCard = $params['twitter_card'] ?? 'summary_large_image';

        $request = Context::getCurrent()->getRequest();
        $protocol = $request->isHttps() ? 'https' : 'http';
        $serverName = defined('SITE_SERVER_NAME') && strlen(SITE_SERVER_NAME) > 0 ? SITE_SERVER_NAME : ($_SERVER['SERVER_NAME'] ?? '');
        $pageUrl = $APPLICATION->GetCurPage(false);
        $fullPageUrl = $protocol . '://' . $serverName . $pageUrl;
        $ogImage = $params['og_image'] ?? ($protocol . '://' . $serverName . '/local/templates/main/assets/img/home/home_open-graph_image.png');

        // Базовые теги
        $APPLICATION->SetTitle($h1);
        $APPLICATION->SetPageProperty('TITLE', $metaTitle);
        if ($description !== '') {
            $APPLICATION->SetPageProperty('description', $description);
        }
        if ($robots !== '') {
            $APPLICATION->SetPageProperty('robots', $robots);
        }

        // Open Graph
        $APPLICATION->SetPageProperty('OG:TITLE', $metaTitle);
        if ($description !== '') {
            $APPLICATION->SetPageProperty('OG:DESCRIPTION', $description);
        }
        $APPLICATION->SetPageProperty('OG:TYPE', $ogType);
        $APPLICATION->SetPageProperty('OG:URL', $fullPageUrl);
        $APPLICATION->SetPageProperty('OG:SITE_NAME', $serverName);
        $APPLICATION->SetPageProperty('OG:LOCALE', 'ru_RU');
        $APPLICATION->SetPageProperty('OG:IMAGE', $ogImage);

        // Twitter
        $APPLICATION->SetPageProperty('TWITTER:CARD', $twitterCard);
        $APPLICATION->SetPageProperty('TWITTER:TITLE', $metaTitle);
        if ($description !== '') {
            $APPLICATION->SetPageProperty('TWITTER:DESCRIPTION', $description);
        }
        $APPLICATION->SetPageProperty('TWITTER:IMAGE', $ogImage);
    }
}
