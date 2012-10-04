<?php
/**
 * Снипет выводящий корзину. Один из основных
 * @author SerovAlexander <serov.sh@gmail.com>
 * Вызов снипета:
 * [!MShopCart?cart_tpl=`min_cart_tpl`&products_tpl=`min_products_tpl`!]
 * Более подробная информация на сайте: http://mshop.rfweb.su/doc
 */
$config = array();
$config['id'] = isset($id) ? $id : 'MShop_cart';
$config['cart_tpl'] = isset($cart_tpl) ? $cart_tpl : 'cart_tpl';
$config['products_tpl'] = isset($products_tpl) ? $products_tpl : 'products_tpl';
$config['empty_cart_tpl'] = isset($empty_cart_tpl) ? $empty_cart_tpl : 'empty_cart_tpl';
$config['site_url'] = isset($site_url) ? $site_url : $modx->config['site_url'];
$config['get_cart_html'] = isset($get_cart_html) ? $get_cart_html : 1;

require_once MODX_BASE_PATH . 'assets/modules/shop/models/MShopModel.class.php';
$mshop = new MShopModel($modx);

echo '
    <script type="text/javascript">
    var site_url="' . $config['site_url'] . '";
    var cart_tpl="' . $config['cart_tpl'] . '";
    var products_tpl="' . $config['products_tpl'] . '";
    var empty_cart_tpl="' . $config['empty_cart_tpl'] . '";
    var mshop_id="' . $config['id'] . '";
    </script>
    <script type="text/javascript" src="/assets/modules/shop/js/front.js"></script>';
    $modx->invokeEvent("OnMShopCartFrontInit");
    
try {
    $result = $mshop->getCartInfo();    
    if ($config['get_cart_html'] == 1 && $mshop->validString($config['cart_tpl']) && $mshop->validString($config['products_tpl']))
        $html = $mshop->getCartHtml($result, $config['cart_tpl'], $config['products_tpl'], $config['empty_cart_tpl']);

    echo '<div id="'.$config['id'].'">' . $html . '</div>';
} catch (Exception $e) {
    echo $e->getMessage();
}

if (!function_exists('saveOrder')) {

    function saveOrder(&$fields) {
        global $modx;
        try {
            $mshop = new MShopModel($modx);
            $mshop->order->newOrder($fields);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}
?>
