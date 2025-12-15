<?php
if (!isset($_GET["referer1"]) || $_GET["referer1"] == "") $_GET["referer1"] = "yandext";
$strReferer1 = htmlspecialchars($_GET["referer1"]);
if (!isset($_GET["referer2"]) || $_GET["referer2"] == "") $_GET["referer2"] = "";
$strReferer2 = htmlspecialchars($_GET["referer2"]);
header("Content-Type: text/xml; charset=windows-1251");
?>
<?xml version="1.0" encoding="windows-1251"?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="2025-12-14 22:59">
<shop>
<name>gis-mining.ru</name>
<company>gis-mining.ru</company>
<url>http://gis-mining.ru</url>
<platform>1C-Bitrix</platform>
<currencies>
<currency id="RUB" rate="1" />
<currency id="USD" rate="85.6647" />
<currency id="EUR" rate="78.32" />
<currency id="UAH" rate="2.511" />
<currency id="BYN" rate="32.2" />
</currencies>
<categories>
<category id="4">Контейнеры</category>
</categories>
<offers>
<offer id="323" available="true">
<url>http://gis-mining.ru/1/detail.php?ID=323&amp;r1=<?echo $strReferer1; ?>&amp;r2=<?echo $strReferer2; ?></url>
<price>4450000</price>
<currencyId>RUB</currencyId>
<categoryId>4</categoryId>
<picture>http://gis-mining.ru/upload/iblock/375/o7k2m16i9ib13j8yjari6ag6xwxfp627.png</picture>
<name>Контейнер 40 футов НС Flagman</name>
<description></description>
</offer>
<offer id="324" available="true">
<url>http://gis-mining.ru/1/detail.php?ID=324&amp;r1=<?echo $strReferer1; ?>&amp;r2=<?echo $strReferer2; ?></url>
<price>2350000</price>
<currencyId>RUB</currencyId>
<categoryId>4</categoryId>
<picture>http://gis-mining.ru/upload/iblock/a6b/fiyp2494gmj1flpwzodhss3gdwld57tv.png</picture>
<name>Контейнер 20 футов DС</name>
<description></description>
</offer>
<offer id="328" available="true">
<url>http://gis-mining.ru/1/detail.php?ID=328&amp;r1=<?echo $strReferer1; ?>&amp;r2=<?echo $strReferer2; ?></url>
<price>3800000</price>
<currencyId>RUB</currencyId>
<categoryId>4</categoryId>
<picture>http://gis-mining.ru/upload/iblock/375/o7k2m16i9ib13j8yjari6ag6xwxfp627.png</picture>
<name>Контейнер 40 футов НС Premium</name>
<description></description>
</offer>
<offer id="329" available="true">
<url>http://gis-mining.ru/1/detail.php?ID=329&amp;r1=<?echo $strReferer1; ?>&amp;r2=<?echo $strReferer2; ?></url>
<price>4020000</price>
<currencyId>RUB</currencyId>
<categoryId>4</categoryId>
<picture>http://gis-mining.ru/upload/iblock/375/o7k2m16i9ib13j8yjari6ag6xwxfp627.png</picture>
<name>Контейнер 40 футов HC/T21 Premium</name>
<description></description>
</offer>
<offer id="330" available="true">
<url>http://gis-mining.ru/1/detail.php?ID=330&amp;r1=<?echo $strReferer1; ?>&amp;r2=<?echo $strReferer2; ?></url>
<price>2530000</price>
<currencyId>RUB</currencyId>
<categoryId>4</categoryId>
<picture>http://gis-mining.ru/upload/iblock/a6b/fiyp2494gmj1flpwzodhss3gdwld57tv.png</picture>
<name>Контейнер 20 футов DС/T21</name>
<description></description>
</offer>
</offers>
</shop>
</yml_catalog>
