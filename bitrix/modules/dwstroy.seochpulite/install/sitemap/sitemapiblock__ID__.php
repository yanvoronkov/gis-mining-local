<?
$arHost = explode( ":", $_SERVER["HTTP_HOST"]);
$_SERVER["HTTP_HOST"] = $arHost[0];
$hostname = $_SERVER['HTTP_HOST'];

function echoSitemapFile($file) {
    if (! file_exists($file)) return false;
    if (! is_readable($file)) return false;

    $timestamp = filemtime($file);
    $tsstring = gmdate('D, d M Y H:i:s ', $timestamp) . 'GMT';
    $etag = md5($file . $timestamp);

    header('Content-Type: application/xml');
    header("Last-Modified: $tsstring");
    header("ETag: \"{$etag}\"");

    $cont = file_get_contents($file);

    echo str_replace("%2F", "/", $cont);

    return true;
}

$sitemapDefault = dirname(__FILE__) . "/sitemapiblock__ID__.xml";

if(!echoSitemapFile($sitemapDefault))
{
    header('HTTP/1.0 404 Not Found');
}
