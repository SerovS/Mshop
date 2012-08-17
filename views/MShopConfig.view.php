<?php
/**
 * Показывает основные настройки (конфиг)
 * @author SerovAlexander <serov.sh@gmail.com>
 */
Class MShopConfig {

    public $output = '';

    public function __construct($model) {
        $this->model = $model;
    }

    function run() {
        $this->output.= '<div class="sectionHeader">Настройки</div>' .
                '<div class="sectionBody"><div style="display: inline-block; width:100%;">';
        $this->actions();

        //print_r($_POST);


        $this->renderConfigInput();

        $this->output.= '</div></div>';
        return $this->output;
    }

    function actions() {
        if ($_POST['save'] && is_array($_POST['Config'])) {
            $this->model->setConfig($_POST['Config']);
            $this->model->saveConfig();
            $this->output .= '<p>Соранение прошло успешно!</p>';
        }
    }

    function render() {
        $res = $this->model->Parse();
        foreach ($res as $parent => $r) {
            $this->output.='<p>' . $r . '</p>';
        }
    }

    public function renderConfigInput() {
        $this->output.= '<form method="POST">';
        $this->output.= '<table>';
        foreach ($this->model->getConfig() as $key => $conf) {
            $this->output.= '<tr><td>' . $conf[1] . '</td><td><input type="text" value="' . $conf[0] . '" name="Config[' . $key . ']" style="width:300px;"></td></tr>';
        }
        $this->output.='<tr><td></td><td><input type="submit" value="Сохранить" name="save"></td></tr>';
        $this->output.= '</table>
                </form>';
    }

}

?>
