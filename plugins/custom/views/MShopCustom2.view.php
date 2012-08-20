<?php

/**
 * Пример произвольной страницы
 * @author SerovAlexander <serov.sh@gmail.com>
 */
Class MShopCustom2 {

    public $output = '';
    public $external;

    public function __construct() {
        
    }

    function run() {
        try {
            $this->output.= '<div class="sectionHeader">Пример 2</div>' .
                    '<div class="sectionBody"><div style="display: inline-block; width:100%;">';
            $this->actions();
            $this->importCategories();
            $this->output.= '
                                <h3>Пример произвольной страницы 2</h3>
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

    function importCategories() {
        
    }

    function renderResult() {
        $this->output.= '<p>Печать результата</p>';
    }

}

?>
