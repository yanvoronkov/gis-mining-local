<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
?>

<!-- Мобильное нижнее меню -->
<nav class="mobile-bottom-menu">
  <a href="/catalog/" class="menu-item">
    <span class="icon">
      <img src="<?= SITE_TEMPLATE_PATH ?>/include/mobile_menu/cataloge.svg" alt="Каталог" width="48" height="48">
    </span>
    <span class="text">Каталог</span>
  </a>

  <a href="tel:+78007777798" class="menu-item">
    <span class="icon">
      <img src="<?= SITE_TEMPLATE_PATH ?>/include/mobile_menu/phone.svg" alt="Телефон" width="48" height="48">
    </span>
    <span class="text">Позвонить</span>
  </a>

  <a href="https://t.me/gismining_chat_bot" id="tg-link-header-desktop" target="_blank" class="menu-item">
    <span class="icon">
      <img src="<?= SITE_TEMPLATE_PATH ?>/include/mobile_menu/tg-mobile.svg" alt="Telegram" width="48" height="48">
    </span>
    <span class="text">Telegram</span>
  </a>

  <a href="https://api.whatsapp.com/send/?phone=%2B79311116071" id="wa-link-header-desktop" target="_blank" class="menu-item">
    <span class="icon">
      <img src="<?= SITE_TEMPLATE_PATH ?>/include/mobile_menu/whatsapp-mobile.svg" alt="WhatsApp" width="48" height="48">
    </span>
    <span class="text">WhatsApp</span>
  </a>
</nav>
