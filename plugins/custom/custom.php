<?php

/**
 * Вспомогательный плагин. Тонкая настройка магазина.
 * 
 * @author SerovAlexander <serov.sh@gmail.com>
 */
//Начало кода для плагина
$e = &$modx->Event;
$output = '';
switch ($e->name) {

    case 'OnMShopControllerRun':
        $params = array('tabs' => array(
                'MShopCatalog' => 'Каталог',
                //'MShopBrandView' => 'Бренды',
                'MShopOrderView' => 'Заказы',
                //'MShopProperties' => 'Свойства',
                'MShopConfig' => 'Настройки',
                'MShopCustom1' => 'Пример1',
                'MShopCustom2' => 'Пример2',
            //'MShopImport' => 'Импорт'
            )
        );

        require_once(dirname(__FILE__) . '/views/MShopCustom1.view.php');
        require_once(dirname(__FILE__) . '/views/MShopCustom2.view.php');

        if ($_GET['view'] == 'MShopCustom1') {
            $m = new MShopModel($modx);
            $model = new MShopCustom1($m);
            $params['runs'] = $model->run();
        } elseif ($_GET['view'] == 'MShopCustom2') {
            $model = new MShopCustom2();
            $params['runs'] = $model->run();
        }

        $e->output(serialize($params));
    default:
        break;
}
//Конец плагина
?>