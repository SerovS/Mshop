<?php
/**
 * Обработка запросов AJAX
 * Передача данных в корзину и из нее.
 * @author SerovAlexander <serov.sh@gmail.com>
 */
require_once '../../../manager/includes/protect.inc.php';

if (empty($_SERVER['HTTP_REFERER']))
    exit;

define('MODX_MANAGER_PATH', "../../../manager/");
require_once(MODX_MANAGER_PATH . 'includes/config.inc.php');
require_once(MODX_MANAGER_PATH . '/includes/protect.inc.php');
define('MODX_API_MODE', true);
require_once(MODX_MANAGER_PATH . '/includes/document.parser.class.inc.php');

session_name($site_sessionname);
session_id($_COOKIE[session_name()]);
session_start();

$modx = new DocumentParser;
$modx->db->connect();
$modx->getSettings();
$modx->config['site_url'] = isset($request['site_url']) ? $request['site_url'] : '';

/*
  if($charset=="UTF-8"){
  header('Content-Type: text/html; charset=utf-8');
  }elseif($charset=="windows-1251"){
  header('Content-Type: text/html; charset=windows-1251');
  } */
$error = $result = '';
try {
    require_once 'models/MShopModel.class.php';
    $mshop = new MShopModel($modx);

    switch ($_POST['MShop_action']) {
        case "add":
            if (isset($_POST['MShop_variant']) && is_numeric($_POST['MShop_variant']) && $_POST['MShop_variant'] > 0) {
                $count = isset($_POST['MShop_count']) ? $_POST['MShop_count'] : 1;
                $mshop->addCart($_POST['MShop_variant'], $count);
            }

            if (isset($_POST['MShop_variant']) && is_array($_POST['MShop_variant'])) {
                foreach ($_POST['MShop_variant'] as $id_variant => $count) {
                    if (is_numeric($id_variant) && $id_variant > 0 && is_numeric($count) && $count >= 1)
                        $mshop->addCart($id_variant, $count);
                    if (is_numeric($id_variant) && $id_variant > 0 && is_numeric($count) && $count == 0)
                        $mshop->removeCart($id_variant);
                }
            }
            break;
        case "empty":
            $mshop->emptyCart();
            break;
        case "delete":
            if (isset($_POST['MShop_variant']) && is_numeric($_POST['MShop_variant']) && $_POST['MShop_variant'] > 0) {
                $mshop->removeCart($_POST['MShop_variant']);
            }
            break;
        case "refresh":
            $result = $mshop->getCartInfo();

            if ($_POST['MShop_get_cart_html'] == 1 && $mshop->validString($_POST['MShop_cart_tpl']) && $mshop->validString($_POST['MShop_products_tpl']) && $mshop->validString($_POST['MShop_empty_cart_tpl']))
                $result['cart_html'] = $mshop->getCartHtml($result, $_POST['MShop_cart_tpl'], $_POST['MShop_products_tpl'], $_POST['MShop_empty_cart_tpl']);
            break;
        default:
            break;
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

if (empty($error)) {
    if ($_POST['ajax'] == 'MShop') {
        echo json_encode(array('status' => 'ok', 'result' => $result));
    } else {
        header("Location: " . $_SERVER["HTTP_REFERER"]);
    }
} else {
    if ($_POST['ajax'] == 'MShop') {
        echo json_encode(array('status' => 'error', 'error' => $error));
    } else {
        echo $error;
        //header("Location: " . $_SERVER["HTTP_REFERER"]);
    }
}
?>