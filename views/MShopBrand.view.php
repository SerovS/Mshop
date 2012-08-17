<?php
/**
 * Показ брендов
 * @author SerovAlexander <serov.sh@gmail.com>
 */

Class MShopBrandView {

    public $output;

    public function __construct($model) {
        $this->model = $model;
    }

    function run() {
        try {
            $this->output.= '<div class="sectionHeader">Бренды</div>
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

    public function getCatalogUrl($doc) {
        return MShopController::getURL(array('mshop_pid' => $doc['id']));
    }

    public function getRemoveUrl($doc) {
        return MShopController::getURL(array('mshop_remove_id' => $doc['id']));
    }

    public function getEditUrl($doc) {
        return MShopController::getURL(
                        array(
                            'mshop_id' => $doc['id'],
                            'mshop' => 1,
                            'a' => 27,
                            'id' => $this->model->start_page,
                            'last_a' => $_GET['a'],
                            'last_id' => $_GET['id'],
                            'last_view' => $_GET['view'],
                            'mshop_template' => $this->model->brand_template
                        )
        );
    }

    public function render() {
        $res = '<a href="' . $this->getEditUrl(array('id' => 'new')) . '">Новый бренд</a> <br/>'; //lang
        

        $res .= '<table class="zebra" width="100%" cellspacing="0" cellpadding="0">
            <col style="width: 4%;" />
            <col style="width: 56%;" />            
            <col style="width: 40%;" />
            <tr>
            <th>ID</th>
            <th>Имя</th>            
            <th>Действие</th>
            </tr>';

        //$str.='d.add(0,-1," корень","url", "title", "addN()");' . "\n\r";
        $docs = $this->model->document->getDocuments(
                false, $_GET['mshop_pid'], array($this->model->brand_template)
        );
        foreach ($docs as $doc) {
            $url = $this->getEditUrl($doc);
            $title = "Редактировать бренд"; //lang

            $res.='<tr>
                        <td>' . $doc['id'] . '</td>
                        <td><a href="' . $url . '" title="' . $title . '">' . $doc['pagetitle'] . '</a> </td>';

            $res .= '<td>';


            $res .= '<a href="' . $this->getEditUrl($doc) . '">Редактировать</a>
                       <a href="' . $this->getRemoveUrl($doc) . '" class="del">Удалить</a>
                                                         
                            </td>
                                </tr>';
        }
        $res .= '</table>';

        return $res;
    }

}

?>
