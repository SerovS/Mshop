<?php

/**
 * Снипет выводящий товары. Один из основных. Аналог Ditto только для товаров mshop.
 * @author SerovAlexander <serov.sh@gmail.com>
 * Вызов снипета:
 * [[MShopCatalog?tpl=`product_tpl`&parent=`7`&depth=`2`&template=`4`&limit=`10`&tvs=`1`&order=`content.menuindex DESC`]]
 * Более подробная информация на сайте: http://mshop.rfweb.su/doc
 */
$config = array();
$config['id'] = isset($id) ? $id : 'MShop_catalog'; // ID снипета, применяется для вывода нескольких снипетов на странице
$config['tpl'] = isset($tpl) ? $tpl : false; // Название чанка шаблона для вывода элементов. Аналог Дитто
$config['tpl_variant'] = isset($tpl_variant) ? $tpl_variant : false; // Чанк для вывода вариантов товара. Возможные переменные для варианта: name, article, price, stock, unit
$config['depth'] = isset($depth) ? $depth : 1; // глубина уровней каталога
$config['pages'] = isset($pages) ? $pages : 1; // нужна ли пагинация. Вывод страниц каталога: [*MShop_catalog_page*]
$config['parent'] = isset($parent) ? $parent : 0; // ID родительского элемента каталога
$config['template'] = isset($template) ? explode(',', $template) : false; // Можно отфильтровать по шаблону
$config['order'] = isset($order) ? $order : false; // способ сортировки. Например content.menuindex DESC
$config['limit'] = isset($limit) && is_numeric($limit) ? $limit : 15; //Кол-во элементов на странице
$config['tvs'] = isset($tvs) ? explode(',', $tvs) : false; // ID tv параметров для более экономного вызова. Вызов идет в одном запросе. Вывод параметра: [+tv1+]
$config['where'] = isset($where) ? $where : false; // Дополнительные условя для sql запроса. Например для вывода 1 товара:   and content.id in (5) Обязательно начинать с and
$config['gettv'] = isset($gettv) ? $gettv : true; // Включить\Выключить подстановку TV параметров отдельным запросом

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

    if ($config['gettv'] === true) {
        $par = $mshop->document->getTvParams();
    }


    if (isset($_GET[$mshop->get_catalog]) && is_numeric($_GET[$mshop->get_catalog]))
        $active_page_id = $_GET[$mshop->get_catalog];

    foreach ($docs as $doc) {
        if ($config['gettv'] === true && is_array($par[$doc['id']])) {
            foreach ($par[$doc['id']] as $name => $value)
                $doc[$name] = $value;
        }
        $option = '';
        $doc['class'] = '';
        if ($doc['id'] == $active_page_id)
            $doc['class'] = 'active';
        foreach ($doc['variants'] as $id => $var) {
            if ($config['tpl_variant']) {
                $var['id_variant'] = $id;
                $option .= $modx->parseChunk($config['tpl_variant'], $var, '[+', '+]');
            }else
                $option.='<option value="' . $id . '" ' . $selected . '>' . $var['article'] . ' - ' . $var['price'] . '</option>';
        }
        if ($config['tpl_variant'] == false)
            $doc['variants'] = '<select name="MShop_variant">' . $option . '</select>';
        else
            $doc['variants'] = $option;
        $str .= $modx->parseChunk($config['tpl'], $doc, '[+', '+]');
    }
    echo $str;
} catch (Exception $e) {
    echo $e->getMessage();
}
?>