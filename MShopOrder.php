<?php
/**
 * Сниппет для просмотра заказа во фронте. Вспомогательный сниппет.
 * @author SerovAlexander <serov.sh@gmail.com>
 * Вызов снипета:
 *[!MShopOrder?tpl=`view_order`&product_tpl=`min_products_tpl2`!]
 * Более подробная информация на сайте: http://mshop.rfweb.su/doc
 */
$config = array();
$config['id'] = isset($id) ? $id : 'MShop_cart';
$config['tpl'] = isset($tpl) ? $tpl : 'order_tpl';
$config['product_tpl'] = isset($product_tpl) ? $product_tpl : 'product_tpl';

require_once MODX_BASE_PATH . 'assets/modules/shop/models/MShopModel.class.php';
$mshop = new MShopModel($modx);

try {
    if (isset($_GET['id_order']) && is_numeric($_GET['id_order']) && $_GET['id_order'] > 0)
        $id = intval($_GET['id_order']);
    else
        throw new Exception('ID Заказа задан неверно'); //lang
    $order = $mshop->order->getOrder($id);
    if ($order['access_key'] == $_GET['access_key']) {
        $order['status_name'] = $mshop->order->status_name[$order['status']];
        $order['create_date'] = date('d.m.Y');
        $order['products'] = '';
        foreach ($order['products_details'] as $p) {
            $order['products'] .= $modx->parseChunk($config['product_tpl'], $p, '[+', '+]');
        }
        foreach ($order['user_details'] as $name => $d) {
            $order['user_details_' . $name] = $d;
        }
        
        $return_order = $modx->invokeEvent("OnMShopOrderFrontView", $order);
        $order = array_merge($order, unserialize($return_order[0]));

        echo $modx->parseChunk($config['tpl'], $order, '[+', '+]');
    }else
        throw new Exception('Секретный ключ заказа задан неверно');
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
