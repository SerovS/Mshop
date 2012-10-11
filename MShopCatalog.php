<?php
/**
 * Снипет выводящий товары. Один из основных. Аналог Ditto только для товаров mshop.
 * @author SerovAlexander <serov.sh@gmail.com>
 * Вызов снипета:
 * [[MShopCatalog?tpl=`product_tpl`&parent=`7`&depth=`2`&template=`4`&limit=`10`&tvs=`1`&order=`content.menuindex DESC`]]
 * Более подробная информация на сайте: http://mshop.rfweb.su/doc
 */
$config = array();
$config['id'] = isset($id) ? $id : 'MShop_catalog';
$config['tpl'] = isset($tpl) ? $tpl : false;
$config['depth'] = isset($depth) ? $depth : 1;
$config['pages'] = isset($pages) ? $pages : 1;
$config['parent'] = isset($parent) ? $parent : 0;
$config['template'] = isset($template) ? explode(',', $template) : false;
$config['order'] = isset($order) ? $order : false;
$config['limit'] = isset($limit) && is_numeric($limit) ? $limit : 15;
$config['tvs'] = isset($tvs) ? explode(',', $tvs) : false;
$config['where'] = isset($where) ? $where : false;

require_once MODX_BASE_PATH . 'assets/modules/shop/models/MShopModel.class.php';
$mshop = new MShopModel($modx);



if ($config['depth'] > 1) {
    $config['parent'] = array($parent => $parent);
    for ($i = 1; $i < $config['depth']; $i++) {
        $add = $mshop->document->getChildsId($config['parent']);
        $config['parent'] = array_merge($config['parent'], $add);
    }
}

try {
    $start = 0;
    if (isset($_GET[$config['id'] . '_start']) && is_numeric($_GET[$config['id'] . '_start']) && $_GET[$config['id'] . '_start'] > 0)
        $start = intval($_GET[$config['id'] . '_start']);
    $docs = $mshop->document->getDocuments(false, $config['parent'], $config['template'], $config['order'], $config['limit'], $start, $config['tvs'], false, $config['where']);

    if ($config['pages'] == 1) {
        $count = $mshop->document->getCount(false, $config['parent'], $config['template']);
        $current_url = $modx->makeUrl($mshop->start_page); //$mshop->document->makeFrontUrl($modx->documentObject);
        $pager = $mshop->document->getFrontPager($count, $config['limit'], $start, $config['id'], $current_url);
        $modx->setPlaceholder($config['id'] . '_page', $pager);
    }
    $str = '';

    foreach ($docs as $doc) {
        $option = '';
        foreach ($doc['variants'] as $id => $var) {
            $option.='<option value="' . $id . '" ' . $selected . '>' . $var['article'] . ' - ' . $var['price'] . '</option>';
        }
        $doc['variants'] = '<select name="MShop_variant">' . $option . '</select>';
        $str .= $modx->parseChunk($tpl, $doc, '[+', '+]');
    }
    echo $str;
} catch (Exception $e) {
    echo $e->getMessage();
}
?>