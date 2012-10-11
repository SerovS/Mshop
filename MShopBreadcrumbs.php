<?php

/**
 * Снипет выводящий путь до товара во фронте
 * @author SerovAlexander <serov.sh@gmail.com>
 * Пример вызова:
 * [!MShopBreadcrumbs!]
 */
$config = array();
$config['id'] = isset($id) ? $id : 'MShop_breadcrumbs';
$config['tpl'] = isset($tpl) ? $tpl : false;
$config['root'] = isset($root) ? $root : $modx->config['site_name']; //название сайта
$config['cat_name'] = isset($cat_name) ? $cat_name : 'Каталог'; // название каталога


require_once MODX_BASE_PATH . 'assets/modules/shop/models/MShopModel.class.php';
$mshop = new MShopModel($modx);

try {
    $res = '<a href="/" title="' . $config['root'] . '">' . $config['root'] . '</a> &raquo; <a href="' . $modx->makeUrl($mshop->start_page) . '">' . $config['cat_name'] . '</a>';

    $paths = $mshop->document->getParents($modx->documentObject);
    krsort($paths);
    foreach ($paths as $path) {
        if (isset($path['id']) && is_numeric($path['id']))
            $res.=' &raquo; ' . '<a href="' . $mshop->document->makeFrontUrl($path) . '">' . $path['pagetitle'] . '</a>';
    }
    $res .= ' &raquo; ' . $modx->documentObject['pagetitle'];



    echo $res;
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
