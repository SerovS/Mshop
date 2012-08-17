<?php

/**
 * Фаил для синхронизации данных в 1с
 * @author SerovAlexander <serov.sh@gmail.com>
 */
//error_reporting(E_ALL);
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Бла бла бла досупа нет ))))';
    exit;
} else {
    if ($_SERVER['PHP_AUTH_USER'] != 'admin' || $_SERVER['PHP_AUTH_PW'] != 'admin') {
        echo 'неверный логин';
        exit();
    }
}



/* $f=fopen('log2.txt', 'a+');
  $str = printArr($_REQUEST);

  fwrite($f, $str."\n");
  fclose($f); */

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
    // print "success\n";
    // $filename = basename($_GET['filename']);
    // $f = fopen($filename, 'ab');
    //fwrite($f, file_get_contents('php://input'));
    //fclose($f); 
    print "success\n";
}



if ($_GET['type'] == 'catalog' && $_GET['mode'] == 'checkauth') {
    print "success\n";
    print session_name() . "\n";
    print session_id();
}

if ($_GET['type'] == 'catalog' && $_GET['mode'] == 'init') {
    @unlink('import.xml');
    @unlink('offers.xml');
    print "zip=no\n";
    print "file_limit=1000000\n";
}

if ($_GET['type'] == 'catalog' && $_GET['mode'] == 'file') {


    $filename = basename($_GET['filename']);
    $f = fopen($filename, 'ab');
    fwrite($f, file_get_contents('php://input'));
    fclose($f);
    print "success\n";
}
if ($_GET['type'] == 'catalog' && $_GET['mode'] == 'import' && $_GET['filename'] == 'import.xml') {
    print "success\n";
}


if ($_GET['type'] == 'catalog' && $_GET['mode'] == 'import' && $_GET['filename'] == 'offers.xml') {
    print "success\n";
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
    try {
        require_once '../models/MShopModel.class.php';
        $mshop = new MShopModel($modx);
        require_once(dirname(__FILE__) . '../../models/MShopExternal.class.php');
        $mshop->external = new MShopExternal($modx, $mshop);
        $res = $mshop->external->importCategories($mshop->external->xml->Классификатор);
        $res = $mshop->external->importProducts();
        $res = $mshop->external->importPrice();
        // unlink('import.xml');
        //unlink('offers.xml');
        print "success\n";
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
}

function printArr($arr, $str = '') {
    foreach ($_REQUEST as $name => $val) {
        if (is_array($val)) {
            $str .= printArr($val, $str);
        } else {
            $str.=date('d.m.Y H:i:s ') . microtime() . 'name:' . $name . "\n" . 'value:' . $val . "\n";
        }
    }
    return $str;
}
?>

