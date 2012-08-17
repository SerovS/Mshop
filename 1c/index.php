<?php
/**
 * Фаил для синхронизации данных в 1с
 * @author SerovAlexander <serov.sh@gmail.com>
 */

if ($_GET['type'] == 'sale' && $_GET['mode'] == 'checkauth') {
    print "success\n";
    print session_name() . "\n";
    print session_id();
}

if ($_GET['type'] == 'sale' && $_GET['mode'] == 'init') {

    require_once '../../../../manager/includes/protect.inc.php';
    define('MODX_MANAGER_PATH', "../../../../manager/");
    require_once(MODX_MANAGER_PATH . 'includes/config.inc.php');
    require_once(MODX_MANAGER_PATH . '/includes/protect.inc.php');
    define('MODX_API_MODE', true);
    require_once(MODX_MANAGER_PATH . '/includes/document.parser.class.inc.php');

    $modx = new DocumentParser;
    $modx->db->connect();
    $modx->getSettings();
    $modx->config['site_url'] = isset($request['site_url']) ? $request['site_url'] : '';

    require_once '../models/MShopModel.class.php';
    $mshop = new MShopModel($modx);
    require_once(dirname(__FILE__) . '../../models/MShopExternal.class.php');
    $mshop->external = new MShopExternal($modx, $mshop);
    $mshop->external->exportOrder();
    print "zip=no\n";
    print "file_limit=1000000\n";
}

if ($_GET['type'] == 'sale' && $_GET['mode'] == 'query') {
    header("Content-type: text/xml; charset=utf-8");
    print file_get_contents('order.xml');
    exit();
}

if ($_GET['type'] == 'sale' && $_GET['mode'] == 'file') {
    print "success\n";
   
   
}



if ($_GET['type'] == 'catalog' && $_GET['mode'] == 'checkauth') {
    print "success\n";
    print session_name() . "\n";
    print session_id();
}

if ($_GET['type'] == 'catalog' && $_GET['mode'] == 'init') {
    print "zip=no\n";
    print "file_limit=1000000\n";
}

if ($_GET['type'] == 'catalog' && $_GET['mode'] == 'file') {
    $filename = basename($_GET['filename']);
    $f = fopen($filename, 'w+');
    fwrite($f, file_get_contents('php://input'));
    fclose($f);
    print "success\n";
}

if ($_GET['type'] == 'catalog' && $_GET['mode'] == 'import') {
    require_once '../../../../manager/includes/protect.inc.php';
    define('MODX_MANAGER_PATH', "../../../../manager/");
    require_once(MODX_MANAGER_PATH . 'includes/config.inc.php');
    require_once(MODX_MANAGER_PATH . '/includes/protect.inc.php');
    define('MODX_API_MODE', true);
    require_once(MODX_MANAGER_PATH . '/includes/document.parser.class.inc.php');

    $modx = new DocumentParser;
    $modx->db->connect();
    $modx->getSettings();
    $modx->config['site_url'] = isset($request['site_url']) ? $request['site_url'] : '';

    require_once '../models/MShopModel.class.php';
    $mshop = new MShopModel($modx);
    require_once(dirname(__FILE__) . '../../models/MShopExternal.class.php');
    $mshop->external = new MShopExternal($modx, $mshop);

    $res = $mshop->external->importProducts();
    $res = $mshop->external->importCategories($mshop->external->xml->Классификатор);
    foreach ($res as $r) {
        echo $r . "\n";
    }
    $res = $mshop->external->importProducts();
    foreach ($res as $r) {
        echo $r . "\n";
    }
    $res = $mshop->external->importPrice();

    foreach ($res as $r) {
        echo $r . "\n";
    }
    print "success\n";
}
?>
