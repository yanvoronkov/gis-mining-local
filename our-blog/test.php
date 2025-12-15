
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Тест URL-rewrite");
?>

<div style="background: #f0f0f0; padding: 20px; margin: 20px 0; border-radius: 5px;">
    <h2>Тест URL-rewrite</h2>
    <p><strong>URL:</strong> <?= $_SERVER['REQUEST_URI'] ?></p>
    <p><strong>GET параметры:</strong> <?= print_r($_GET, true) ?></p>
    <p><strong>REQUEST параметры:</strong> <?= print_r($_REQUEST, true) ?></p>
    
    <h3>Тестовые ссылки:</h3>
    <ul>
        <li><a href="/our-blog/test-article-1/">Тест статья 1</a></li>
        <li><a href="/our-blog/test-article-2/">Тест статья 2</a></li>
        <li><a href="/our-blog/detail/index.php?ELEMENT_CODE=test-article-1">Прямая ссылка с параметром</a></li>
    </ul>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
