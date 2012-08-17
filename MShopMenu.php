<?php
/**
 * Снипет выводящий меню. Вспомогательный сниппет. Попытка сделать аналог wayfinder
 * @author SerovAlexander <serov.sh@gmail.com>
 * Вызов снипета:
 * [[MShopMenu?tpl=`menu_tpl`&subtpl=`submenu_tpl`&parent=`0`&depth=`2`&template=`5`]]
 * Более подробная информация на сайте: http://mshop.rfweb.su/doc
 */
$config = array();
$config['id'] = isset($id) ? $id : 'MShop_menu';
$config['tpl'] = isset($tpl) ? $tpl : false;
$config['subtpl'] = isset($subtpl) ? $subtpl : $tpl;
$config['depth'] = isset($depth) ? $depth : 1;
$config['pages'] = isset($pages) ? $pages : 1;
$config['parent'] = isset($parent) ? $parent : false;
$config['template'] = isset($template) ? explode(',', $template) : false;
$config['order'] = isset($order) ? $order : 'parent ASC';
$config['limit'] = isset($limit) && is_numeric($limit) ? $limit : 15;
$config['parent'] = array($parent => $parent);


require_once MODX_BASE_PATH . 'assets/modules/shop/models/MShopModel.class.php';
$mshop = new MShopModel($modx);



if ($config['depth'] > 1) {
    for ($i = 1; $i < $config['depth']; $i++) {
        $add = $mshop->document->getChildsId($config['parent']);
        $config['parent'] = array_merge($config['parent'], $add);
    }
}
//print_r($config['parent']);
try {
    $p=0;
    if(isset($_GET[$mshop->get_catalog]) && is_numeric($_GET[$mshop->get_catalog]))
        $p = $_GET[$mshop->get_catalog];
    $start = 0;
    $docs = $mshop->document->getDocuments(false, $config['parent'], $config['template'], $config['order'], $config['limit'], $start);

    $str = '';
    foreach ($docs as $doc) {
        $d[$doc['id']]['item'] = $doc;
        $d[$doc['parent']]['childs'][$doc['id']] = $doc;
    }
    $ignore=array();
    foreach ($d as $doc) {
        if (isset($doc['item']) && !in_array($doc['item']['id'], $ignore)) {
            if($doc['item']['id'] == $p)
                $doc['item']['class'] = 'active';
            $doc['subitems'] = '';
            if (is_array($doc['childs'])) {
                foreach ($doc['childs'] as $subd) {
                    $ignore[]=$subd['id'];
                    if($subd['id'] == $p)
                        $subd['class'] = 'active';
                    $doc['subitems'] .= $modx->parseChunk($config['subtpl'], $subd, '[+', '+]');
                }
                $doc['item']['subitems'].='<ul>' . $doc['subitems'] . '</ul>';
            }
            $str .= $modx->parseChunk($config['tpl'], $doc['item'], '[+', '+]');
        }
    }


    echo $str;
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
