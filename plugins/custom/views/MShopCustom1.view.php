<?php

/**
 * Пример произвольной страницы
 * @author SerovAlexander <serov.sh@gmail.com>
 */
Class MShopCustom1 {

    public $output = '';
    public $external;

    public function __construct($model) {
        $this->model = $model;
    }

    function run() {

        try {
            $this->output.= '<div class="sectionHeader">Пример пустой страницы</div>' .
                    '<div class="sectionBody"><div style="display: inline-block; width:100%;">';
            $this->actions();
            $this->output.= '
                                <h3>Пример произвольной страницы 1</h3>
                         <a href="' . MShopController::getURL(array('action' => 'action1')) . '">Действие 1</a> <br>
                         <a href="' . MShopController::getURL(array('action' => 'action2')) . '">Действие 2</a> <br>                        
                     </div></div>';
            return $this->output;
        } catch (Exception $e) {
            if ($e->getCode() == 1) {
                return MShopController::message('Ошибка', $e->getMessage(), 'error', true);
            }else
                return MShopController::message('Сообщение', $e->getMessage(), 'warning', false) . $this->output;
        }
    }

    function actions() {
        if ($_GET['action'] == 'action1') {
            $this->output.= '<p>Сейчас происходит действие 1</p>';
        }
        if ($_GET['action'] == 'action2') {
            $this->output.= '<p>Сейчас происходит действие 2</p>';
        }
        $this->renderResult();
    }

    function renderResult() {
        $docs = $this->model->document->getDocuments(false, 1, array(
            $this->model->category_template,
            $this->model->product_template
                ), false, 1000, 0, false, true);
        foreach ($docs as $doc) {
            $this->output.= '<p>' . $doc['name'] . '</p>';
        }
        $this->output.= '<p> ss </p>';
    }

}

?>
