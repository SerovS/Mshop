<?php

/**
 * Показ кталога продукции
 * @author SerovAlexander <serov.sh@gmail.com>
 */
Class MShopCatalog {

    public $output;

    public function __construct($model) {
        $this->model = $model;
    }

    function run() {
        try {
            $this->output.= '<div class="sectionHeader">Название страницы модуля</div>
                                    <div class="sectionBody">
                                            <div style="display: inline-block; width:100%;">';
            $this->actions();
            $this->output.= $this->render();
            $this->output.= '</div></div>';
            return $this->output;
        } catch (Exception $e) {
            if ($e->getCode() == 1) {
                return MShopController::message('Ошибка', $e->getMessage(), 'error', true);
            }else
                return MShopController::message('Сообщение', $e->getMessage(), 'warning', false) . $this->output;
        }
    }

    function actions() {

        if (isset($_GET['mshop_remove_id']) && is_numeric($_GET['mshop_remove_id'])) {
            $this->model->document->remove($_GET['mshop_remove_id']);
        }
    }

    public function getBreadcrumbs() {
        $res = '<a href="' . $this->getCatalogUrl(array('id' => 0)) . '">Корень</a>';
        if (isset($_GET['mshop_pid']) && is_numeric($_GET['mshop_pid'])) {

            $doc = $this->model->document->getDocuments($_GET['mshop_pid']);
            $paths = $this->model->document->getParents($doc[$_GET['mshop_pid']]);
            asort($paths);
            foreach ($paths as $path) {
                if (isset($path['id']) && is_numeric($path['id']))
                    $res.='->' . '<a href="' . $this->getCatalogUrl($path, 0) . '">' . $path['pagetitle'] . '</a>';
            }
            $res .= '->' . $doc[$_GET['mshop_pid']]['pagetitle'];
        }


        return $res;
    }

    public function getCatalogUrl($doc, $mshop_start = false) {
        return MShopController::getURL(array('mshop_pid' => $doc['id'], 'mshop_start' => $mshop_start));
    }

    public function getRemoveUrl($doc) {
        return MShopController::getURL(array('mshop_remove_id' => $doc['id']));
    }

    public function getEditUrl($doc) {
        if (is_array($this->model->product_template))
            $current_template = current($this->model->product_template);
        else
            $current_template = $this->model->product_template;
        return MShopController::getURL(
                        array(
                            'mshop_id' => $doc['id'],
                            'mshop' => 1,
                            'a' => 27,
                            'id' => $this->model->start_page,
                            'last_a' => $_GET['a'],
                            'last_id' => $_GET['id'],
                            'last_view' => $_GET['view'],
                            'mshop_template' => $current_template
                        )
        );
    }

    public function render() {
        $res = '<a href="' . $this->getEditUrl(array('id' => 'new')) . '">Новый документ</a> <br/>';
        $res .= '<p>Путь: ' . $this->getBreadcrumbs() . '</p>';

        $res .= '<table class="zebra" width="100%" cellspacing="0" cellpadding="0">
            <col style="width: 4%;" />
            <col style="width: 46%;" />
            <col style="width: 10%;" />
            <col style="width: 10%;" />
            <col style="width: 30%;" />
            <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Цена</th>
            <th>Количество</th>
            <th>Действие</th>
            </tr>';

        //$str.='d.add(0,-1," корень","url", "title", "addN()");' . "\n\r";
        $start = isset($_GET['mshop_start']) ? $_GET['mshop_start'] : 0;
        if (is_array($this->model->product_template)) {
            foreach ($this->model->product_template as $t)
                $templates[] = $t;
        }else
            $templates[] = $this->model->product_template;
        if (is_array($this->model->category_template)) {
            foreach ($this->model->category_template as $t)
                $templates[] = $t;
        }else
            $templates[] = $this->model->category_template;


        $docs = $this->model->document->getDocuments(
                false, $_GET['mshop_pid'], $templates, false, $this->model->limit, $start, false, true
        );
        foreach ($docs as $doc) {
            if ($this->model->document->isProduct($doc)) {
                $url = $this->getEditUrl($doc);
                $title = "Редактировать товар"; //lang
            } else {
                $url = $this->getCatalogUrl($doc, $start);
                $title = "Войти в каегорию"; //lang
            }
            $res.='<tr>
                        <td>' . $doc['id'] . '</td>
                        <td><a href="' . $url . '" title="' . $title . '">' . $doc['pagetitle'] . '</a> </td>';
            if ($this->model->document->isProduct($doc))
                $res .='  <td><input type="text" name="mshop_price[' . $doc['id'] . ']" value="' . $doc['price'] . '"></td>
                            <td><input type="text" name="mshop_stock[' . $doc['id'] . ']" value="' . $doc['stock'] . '"></td>';
            else
                $res .='  <td></td>
                            <td></td>';
            $res .= '<td>';
            if ($this->model->document->isCategory($doc))
                $res .='<a href="' . $this->getCatalogUrl($doc, $start) . '" >Войти</a> ';

            $res .= '<a href="' . $this->getEditUrl($doc) . '">Редактировать</a>
                       <a href="' . $this->getRemoveUrl($doc) . '" class="del">Удалить</a>
                                                         
                            </td>
                                </tr>';
        }
        $res .= '</table>';
        $res.='<div class="pager">' . $this->model->getPager($this->model->document->getCount(
                                false, $_GET['mshop_pid'], $tempaltes), $this->model->limit, $start) . '</div>';
        /*
          $res.='<link rel="stylesheet" type="text/css" href="../assets/modules/shop/css/dtree.css" media="all" />
          <script type="text/javascript" src="../assets/modules/shop/js/dtree.js"></script>

          <script>
          function addN() {
          alert(d);

          //d.add(15,0," корень","url", "title");

          }
          </script>
          <p><a onclick="addN();">Новый</a></p>
          <p><a href="javascript: d.openAll();">open all</a> | <a href="javascript: d.closeAll();">close all</a></p>
          <div class="dtree">
          <script type="text/javascript">

          d = new dTree("d");
          ' . $str . '
          document.write(d);
          </script>

          </div>'; */

        return $res;
    }

}

?>
