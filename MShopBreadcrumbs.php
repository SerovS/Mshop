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


require_once MODX_BASE_PATH . 'assets/modules/shop/models/MShopModel.class.php';
$mshop = new MShopModel($modx);

try {
    $res = '<a href="/" title="Сеть магазинов Форма для Жизни">Сеть магазинов Форма для Жизни</a> &raquo; <a href="' . $modx->makeUrl($mshop->start_page) . '">Каталог</a>';
            
        $paths = $mshop->document->getParents($modx->documentObject);          
        krsort($paths);        
        foreach ($paths as $path) {
            if (isset($path['id']) && is_numeric($path['id']))
                $res.=' &raquo; ' . '<a href="'.$mshop->document->makeFrontUrl($path).'">' . $path['pagetitle'] . '</a>';
        }
        $res .= ' &raquo; ' .$modx->documentObject['pagetitle'];
    

  
    echo $res;
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
