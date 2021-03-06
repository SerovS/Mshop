<?php

/**
 * Плагин отвечает за вывод документов во фронт. 
 * Сохранение документов в таблицы mshop. 
 * Добавление полей при редактировании документов.
 * @author SerovAlexander <serov.sh@gmail.com>
 */
$e = &$modx->Event;
$output = '';

switch ($e->name) {

    case 'OnLoadWebDocument':
        require_once MODX_BASE_PATH . 'assets/modules/shop/models/MShopModel.class.php';
        $mshop = new MShopModel($modx);
        $id_mshop = false;
        if (is_string($_GET[$mshop->get_catalog]) && strlen($_GET[$mshop->get_catalog]) > 1)
            $id_mshop = $mshop->document->getIdDocumentByAlias($_GET[$mshop->get_catalog]);

        if (($_GET[$mshop->get_catalog]) && is_numeric($_GET[$mshop->get_catalog]))
            $id_mshop = $_GET[$mshop->get_catalog];


        if ($id_mshop) {

            $doc = $mshop->document->getDocuments($id_mshop);
            $doc = $doc[$id_mshop];

            $option = '';
            if (empty($doc))
                break;
            foreach ($doc['variants'] as $id => $var) {
                $option.='<option value="' . $id . '" ' . $selected . '>' . $var['name'] . ' - ' . $var['price'] . '</option>';
            }
            $doc['variants'] = '<select name="MShop_variant">' . $option . '</select>';

            $modx->documentContent = $mshop->document->getTemplate($doc['template']);
            $modx->documentObject = array_merge($modx->documentObject, $doc);
            $tvs = $mshop->document->getTVs($doc);
            $modx->documentObject = array_merge($modx->documentObject, $tvs);
        }
        break;

#######################################################

    case 'OnBeforeDocFormSave':
        if (isset($_POST['mshop']) && $_POST['mshop'] == 1) {
            global $tmplvars;
            require_once MODX_BASE_PATH . 'assets/modules/shop/models/MShopModel.class.php';
            $mshop = new MShopModel($modx);
            $_POST['parent'] = $_POST['mshop_parent'];
            if (isset($_REQUEST['mshop_id']) && is_numeric($_REQUEST['mshop_id'])) {
                $res = $mshop->document->updateDocument($_POST, $_POST['mshop_id']);
                $id_content = $_POST['mshop_id'];
                echo 'Обновление ' . $res['pagetitle'] . ' прошло успешно!'; //lang
            } else {
                $id_content = $mshop->document->insertDocument($_POST);
                echo 'Добавление нового документа успешно! ID' . $id_content; //lang
            }

            if (isset($_POST['mshop_variant']) && is_array($_POST['mshop_variant']))
                $mshop->variant->saveVariants($_POST['mshop_variant'], $id_content);
            if (isset($_POST['remove_mshop_variants']))
                $mshop->variant->removeVariants($_POST['remove_mshop_variants']);
            if (isset($_POST['mshop_brand']) && is_numeric($_POST['mshop_brand']))
                $mshop->brand->saveBrand($_POST['mshop_brand'], $id_content);

            if (isset($_POST['mshop_properties']) && is_array($_POST['mshop_properties']))
                $mshop->property->saveProperties($_POST['mshop_properties'], $id_content);


            if (is_array($tmplvars))
                $mshop->document->saveTVs($tmplvars, $id_content);

            //формируем редирект

            if ($_POST['stay'] == 2) {
                $link = 'index.php?mshop_id=' . $id_content . '&mshop=1&a=27&id=' . $mshop->start_page . '&last_a=' . $_POST['mshop_last_a'] . '&last_id=' . $_POST['mshop_last_id'] . '&last_view=MShopCatalog';
                echo '<script type="text/javascript">setTimeout(function(){document.location = "' . $link . '";},500);</script>';
            } elseif ($_POST['stay'] == 1) {
                echo '<p>Создание нового документа не возможно! Пока.</p>';
                echo '<script type="text/javascript">setTimeout(function(){document.location = "' . $_POST['mshop_redirect'] . '&mshop_pid=' . $_REQUEST['mshop_parent'] . '";},500);</script>';
            }else
                echo '<script type="text/javascript">setTimeout(function(){document.location = "' . $_POST['mshop_redirect'] . '&mshop_pid=' . $_REQUEST['mshop_parent'] . '";},500);</script>';

            exit;
        }
        break;

#######################################################

    case 'OnDocFormPrerender':
        if (isset($_REQUEST['mshop']) && $_REQUEST['mshop'] == 1) {
            global $id, $content, $tbl_site_tmplvar_contentvalues;

            require_once MODX_BASE_PATH . 'assets/modules/shop/models/MShopModel.class.php';
            $mshop = new MShopModel($modx);
            if (isset($_REQUEST['mshop_id']) && is_numeric($_REQUEST['mshop_id'])) {
                $id = $_REQUEST['mshop_id'];
                $doc = $mshop->document->getDocuments($_REQUEST['mshop_id']);
                $doc = $doc[$_REQUEST['mshop_id']];
                $doc['id'] = $content['id'];

                $doc['mshop_properies'] = $mshop->document->getProperties($_REQUEST['mshop_id']);
                $output .= '<input type="hidden" value="' . $_REQUEST['mshop_id'] . '" name="mshop_id">';
            } else {
                $doc['pagetitle'] = $doc['longtitle'] = $doc['content'] = $doc['introtext'] = $doc['alias'] = '';
                $doc['richtext'] = 1;
                if (is_array($mshop->product_template))
                    $current_template = $mshop->product_template[0];
                else
                    $current_template = $mshop->product_template;
                $doc['template'] = isset($_GET['mshop_template']) ? $_GET['mshop_template'] : $current_template;
            }


            $content = array_merge($content, $doc); //подменяем значениями из таблицы mshop
            //устанавливаем пременную mshop_parent. Родитель в таблице mshop
            if (isset($_GET['mshop_pid']))
                $content['mshop_parent'] = $_GET['mshop_pid'];
            elseif (isset($_REQUEST['mshop_parent']))
                $content['mshop_parent'] = $_REQUEST['mshop_parent'];
            else
                $content['mshop_parent'] = $content['parent'];

            $content['parent'] = 0; //определяем для обхода ошибок в ядре modx
            //определяем переменные mshop
            $output .= '<input type="hidden" value="1" name="mshop">';
            $output .= '<input type="hidden" value="' . $content['mshop_parent'] . '" name="mshop_pid">';

            $mshop_pid = isset($_GET['mshop_pid']) ? $_GET['mshop_pid'] : 0;
            //ставим исходный путь для mshop модуля
            if (isset($_REQUEST['mshop_redirect']))
                $redirect = $_REQUEST['mshop_redirect'];
            else
                $redirect = 'index.php?a=' . $_GET['last_a'] . '&id=' . $_GET['last_id'] . '&view=' . $_GET['last_view'] . '&mshop_pid=' . $mshop_pid;
            $output .= '<input type="hidden" value="' . $redirect . '" name="mshop_redirect">';
            $output .= '<input type="hidden" value="' . $_GET['last_id'] . '" name="mshop_last_id">';
            $output .= '<input type="hidden" value="' . $_GET['last_a'] . '" name="mshop_last_a">';
            $tbl_site_tmplvar_contentvalues = $mshop->modx->getFullTableName(MShopModel::TV); //таблица mshop tv
        }
        break;

#######################################################
    default:
        break;
}
$e->output($output);
?>