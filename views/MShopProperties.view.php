<?php
/**
 * Страница вывода сойств товара
 * @author SerovAlexander <serov.sh@gmail.com>
 */
Class MShopProperties {

    public $output;

    public function __construct($model) {
        $this->model = $model;
    }

    function run() {
        try {
            $this->output.= '<div class="sectionHeader">Свойства</div>
                                    <div class="sectionBody">
                                            <div style="display: inline-block; width:100%;">';
          
            $this->actions();
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
        if (isset($_POST['MShopProperty']['action']) && $_POST['MShopProperty']['action'] == 'save') {
            $this->model->property->saveProperty($_POST['MShopProperty']);
        }

        if (isset($_GET['action']) && $_GET['action'] == 'delete' && is_numeric($_GET['id_property']) && $_GET['id_property'] > 0) {
            $model = $this->model->property->removeProperty($_GET['id_property']);
        }

        if (isset($_GET['action']) && $_GET['action'] == 'edit') {
            $model = $this->model->property->getProperty($_GET['id_property']);
            $this->output.=$this->edit($model);
        } else {
            $this->render();
        }
    }

    public function edit($model) {

        $cats = $this->model->document->getCategories();
        if (is_array($model['options']))
            $model['options'] = implode("\n", $model['options']);
        $option = $selected = '';

        foreach ($cats as $cat) {
            if (in_array($cat['id'], $model['categories']))
                $selected = 'selected';
            $option.='<option value="' . $cat['id'] . '" ' . $selected . '>' . $cat['pagetitle'] . '</option>';
            $selected = '';
        }

        $this->output .= '
        <form class="editbox" method="POST" action="'.MShopController::getURL(array('action'=>'')).'">
        
        <h2>' . $model['name'] . '</h2>
        <div class="lc editblock">
        <h3>Свойства</h3>
        <label>Название:<input type="text" name="MShopProperty[name]" value="' . $model['name'] . '"></label> <br/>
        <label>Возможные значения:<textarea name="MShopProperty[options]">' . $model['options'] . '</textarea></label> <br/>
        </div>
        <div class="lc">
        <div class="productsdata">
        <h3>Использовать в категориях:</h3>
        <select style="width:360px; min-height:200px;" multiple="" name="MShopProperty[categories][]">        
            ' . $option . '
        </select>
        </div>
        </div>
        <div class="clear"></div>
        <input type="hidden" value="' . $model['id'] . '" name="MShopProperty[id]">
        <input type="hidden" value="save" name="MShopProperty[action]">
        <input type="submit" value="Сохранить" class="button"> <br/>
        <a href="'.MShopController::getURL(array('id_property' =>'', 'action' => '')).'" class="button"> &larr; Все свойства</a>
        </form>

        ';
    }

    public function render() {
        $this->output.= '<a href="' . MShopController::getURL(array('id_property' => 'new', 'action' => 'edit')) . '" class="button">Новое свойство</a> <br/><br/>';
        $this->output .= '<table class="zebra" width="100%" cellspacing="0" cellpadding="0">
            <col style="width: 5%;" />
            <col style="width: 40%;" />            
            <col style="width: 35%;" />                
            <col style="width: 20%;" />
            <tr>
            <th>ID</th>
            <th>Название</th>                        
            <th>Доступно в категориях</th>                        
            <th>Действия</th>
            </tr>';
        $prs = $this->model->property->getProperties();
        //print_r($prs);
        foreach ($prs as $property) {
            $p = $property['property'];
            $list_cat = '';
            foreach ($property['categories'] as $id => $name)
                $list_cat.='' . $name . '<br />';

            if (is_array($p['options']))
                $type = "Выбор из списка значений";
            else
                $type = "Текстовое поле";

            $this->output.='<tr>
                <td>' . $p['id'] . '</td>
                <td><div class="big"><a href="' . $this->getEditUrl($p) . '">' . $p['name'] . '</a></div><div class="note">' . $type . '</div></td>
                
                <td>' . $list_cat . '</td>
                <td>
                    <a href="' . $this->getEditUrl($p) . '">Редактировать</a>
                    <a href="' . $this->getDeleteUrl($p) . '" class="del">Удалить</a>
                </td>
                </tr>';
        }

        $this->output.='</table>';
    }

    public function getEditUrl($property) {
        return MShopController::getURL(array('id_property' => $property['id'], 'action' => 'edit'));
    }

    public function getDeleteUrl($property) {
        return MShopController::getURL(array('id_property' => $property['id'], 'action' => 'delete'));
    }

}

?>
