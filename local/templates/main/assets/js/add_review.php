<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

\Bitrix\Main\Loader::includeModule('highloadblock');

$hlbl = 2; // ID HL-блока
$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl)->fetch();
$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
$entityClass = $entity->getDataClass();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int)$_POST['PRODUCT_ID'];
    $author = trim($_POST['AUTHOR']);
    $text = trim($_POST['TEXT']);
    $rating = (int)$_POST['RATING'];

    if (!$productId || !$author || !$text || !$rating) {
        echo json_encode(["success" => false, "error" => "Заполните все обязательные поля"]);
        die();
    }

    $result = $entityClass::add([
        "UF_PRODUCT_ID" => $productId,
        "UF_AUTHOR"     => $author,
        "UF_TEXT"       => $text,
        "UF_RATING"     => $rating,
        "UF_DATE"       => new \Bitrix\Main\Type\DateTime()
    ]);

    if ($result->isSuccess()) {
        echo json_encode(["success" => true, "message" => "Спасибо за ваш отзыв!"]);
    } else {
        echo json_encode([
            "success" => false,
            "error"   => implode(", ", $result->getErrorMessages())
        ]);
    }
}
