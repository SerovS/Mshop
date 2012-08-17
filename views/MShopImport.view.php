<?php

/**
 * Выводит на экран страницу с импортом
 * @author SerovAlexander <serov.sh@gmail.com>
 */
Class MShopImport {

    public $output = '';
    public $external;

    public function __construct($model) {
        $this->model = $model;
        require_once(dirname(__FILE__) . '/../models/MShopExternal.class.php');
        $this->external = new MShopExternal($this->model->modx, $this->model);
    }

    function run() {
        try {
            $this->output.= '<div class="sectionHeader">Импорт из 1с</div>' .
                    '<div class="sectionBody"><div style="display: inline-block; width:100%;">';
            $this->actions();
            $this->importCategories();
            $this->output.= '
		  <a href="' . MShopController::getURL(array('action' => 'exportcsv')) . '">Получить файл каталога</a><br>
                                <form method="POST" action="' . MShopController::getURL(array('action' => 'csv')) . '" enctype="multipart/form-data">
                                <input type="file" name="filename"> <input type="submit" value="импорт"></form>
                                <br><br><br><hr>
                                <h3>Функции применяются только веб-мастерами. Использование этих сылок может повредить ваши данные</h3>
                         <a href="' . MShopController::getURL(array('action' => 'categories')) . '">импорт категорий</a> <br>
                         <a href="' . MShopController::getURL(array('action' => 'products')) . '">импорт продуктов</a> <br>
                         <a href="' . MShopController::getURL(array('action' => 'price')) . '">импорт цен</a><br>                     
                         <a href="' . MShopController::getURL(array('action' => 'empty')) . '">Очистить каталог</a><br>
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
        if ($_GET['action'] == 'products') {
            $r = $this->external->importProducts();
            $this->renderResult($r);
        }
        if ($_GET['action'] == 'categories') {
            $r = $this->external->importCategories($this->external->xml->Классификатор);
            $this->renderResult($r);
        }
        if ($_GET['action'] == 'price') {
            $r = $this->external->importPrice();
            $this->renderResult($r);
        }
        if ($_GET['action'] == 'csv') {
            $r = $this->external->importCsv();
            $this->renderResult($r);
        }

        if ($_GET['action'] == 'exportcsv') {
            $r = $this->external->exportCsv();
            $this->renderResult($r);
        }
        if ($_GET['action'] == 'empty') {
            $r = $this->external->emptyCatalog();
            $this->renderResult($r);
        }
    }

    function importCategories() {
        
    }

    function renderResult($res) {
        foreach ($res as $r)
            if (is_array($r))
                $this->renderResult($r);
            else
                $this->output.=$r . '<br/>';
    }

}

?>
