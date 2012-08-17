<?php
/**
 * Показ и работа с заказами
 * @author SerovAlexander <serov.sh@gmail.com>
 */
Class MShopOrderView {

    public $output;

    public function __construct($model) {
        $this->model = $model;
    }

    function run() {
        try {
            $this->output.= '<div class="sectionHeader">Заказы</div>
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

        if (isset($_GET['action']) && $_GET['action'] == 'save' && is_array($_POST['Order'])) {
            $this->model->order->saveOrders($_POST['Order']);
        }

        if (isset($_GET['id_order']) && is_numeric($_GET['id_order']) && $_GET['action'] == 'delete') {
            $this->model->order->removeOrder($_GET['id_order']);
            $this->output .= '<p>Заказ ' . $_GET['id_order'] . ' удален. </p>';
        }

        if (isset($_GET['id_order']) && is_numeric($_GET['id_order']) && $_GET['id_order'] > 0 && $_GET['action'] == 'edit') {
            $this->output.= $this->renderEditOrder($_GET['id_order']);
        }else
            $this->output.= $this->renderOrders();
    }

    public function getCatalogUrl($doc) {
        return MShopController::getURL(array('mshop_pid' => $doc['id']));
    }

    public function getRemoveUrl($order) {
        return MShopController::getURL(array('id_order' => $order['id'], 'action' => 'delete'));
    }

    public function getEditUrl($order) {
        return MShopController::getURL(
                        array(
                            'id_order' => $order['id'],
                            'action' => 'edit'
                        )
        );
    }

    public function renderOrders() {
        $res = '';

        $res .= '<form method="POST" action="' . MShopController::getURL(array('action' => 'save')) . '">';
        $res .= '<table class="zebra" width="100%" cellspacing="0" cellpadding="0">
            <col style="width: 35%;" />
            <col style="width: 35%;" />            
            <col style="width: 10%;" />                
            <col style="width: 20%;" />
            <tr>
            <th>Заказ</th>
            <th>Состав</th>            
            <th>Статус</th>            
            
            <th></th>
            </tr>';

        $start = isset($_GET['mshop_start']) ? $_GET['mshop_start'] : 0;
        $orders = $this->model->order->getOrders(false, $start, $this->model->limit);
        if (empty($orders))
            return '';
        foreach ($orders as $order) {
            $url = $this->getEditUrl($order);
            $products_details = $this->renderProducts($order['products_details']);
            $user_details = $this->renderUser($order);

            $res.='<tr>
                        <td> <a href="' . $url . '" class="big">Заказ №' . $order['id'] . '</a> <br />' . date('d.m.Y H:i:s', strtotime($order['create_date'])) . ' <div class="note"> ' . $user_details . '</div></td>
                        <td>' . $products_details . ' <br /> <span class="big">На сумму: ' . $order['price'] . ' ' . $order['currency'] . '</span> </td>
                        <td><input type="hidden" name="Order[' . $order['id'] . '][last_status]" value="'.$order['status'].'">
                               <select name="Order[' . $order['id'] . '][status]">' . $this->renderOption($this->model->order->status_name, $order['status']) . '</select> </td>
                        ';

            $res .= '<td>';


            $res .= '<a href="' . $this->getEditUrl($order) . '">Редактировать</a>
                       <a href="' . $this->getRemoveUrl($order) . '" class="del">Удалить</a>
                                                         
                            </td>
                                </tr>';
        }
        $res .= '</table>';
        $res.='<input type="submit" value="Сохранить" class="button">';
        $res .= '</form>';
        $res.='<div class="pager">' . $this->model->getPager($this->model->order->getCountOrders(), $this->model->limit, $start) . '</div>';

        return $res;
    }

    public function renderUser($order) {
        $str = $order['name'] . '<br />';
        $str .= $order['email'] . '<br />';
        $str .= $order['phone'] . '<br />';
        $str .= $order['address'] . '<br /><div style="overflow:hidden; height: 35px;">';
        $str .= $order['comment'] . '</div>';
        return $str;
    }

    public function renderProducts($products) {
        if (is_array($products)) {
            foreach ($products as $prod) {
                $str .= '<a href="' . $prod['url'] . '" target="_blank" class="outlink">'
                        . $prod['pagetitle'] . ' ' . $prod['name'] . ' (' . $prod['article'] . ')
                            </a> 
                            &nbsp;&nbsp;&nbsp;' . $prod['count'] . 'шт. x ' . $prod['price'] . ' руб. <br/>';
            }
            return $str;
        }else
            $str.='Пустой заказ';
    }

    public function renderEditOrder($id) {
        $order = $this->model->order->getOrder($id);
        
        $res = '<h1>Заказ №' . $order['id'] . '</h1>';        
        $res .= '<form method="POST" action="' . MShopController::getURL(array('action' => 'save')) . '">';
        $res.='
                <div class="lc">
                <div class="orderdata">
                   <h3>Данные заказа</h3>
                    <table>
                    <tr>
                        <td>Дата</td>
                        <td>' . date('d.m.Y H:i:s', strtotime($order['create_date'])) . '</td>
                     </tr>
                     <tr>
                        <td>IP</td>
                        <td>' . $order['ip'] . '</td>
                     </tr>
                     
                     <tr>
                        <td>Статус</td>
                        <td><input type="hidden" name="Order[' . $order['id'] . '][last_status]" value="'.$order['status'].'">
                               <select name="Order[' . $order['id'] . '][status]">' . $this->renderOption($this->model->order->status_name, $order['status']) . '</select></td>
                     </tr>
                     <tr>
                        <td>ФИО</td>
                        <td><input type="text" name="Order[' . $order['id'] . '][name]" value="' . $order['name'] . '"></td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td><input type="text" name="Order[' . $order['id'] . '][email]" value="' . $order['email'] . '"></td>
                    </tr>
                    <tr>
                        <td>Телефон</td>
                        <td><input type="text" name="Order[' . $order['id'] . '][phone]" value="' . $order['phone'] . '"></td>
                    </tr>
                    <tr>
                        <td>Адрес</td>
                        <td><textarea name="Order[' . $order['id'] . '][address]" >' . $order['address'] . '</textarea></td>
                    </tr>
                    <tr>
                        <td>Комментарий</td>
                        <td><textarea name="Order[' . $order['id'] . '][comment]" >' . $order['comment'] . '</textarea></td>
                    </tr>
                    
                    <tr>
                        <td>Трекинг номер</td>
                        <td><input type="text" name="Order[' . $order['id'] . '][tracking_num]" value="' . $order['tracking_num'] . '"></td>
                    </tr>
                    </table>
                    </div>';
        $res .= '
                    <a onclick="$j(\'.userdata\').show(\'fast\');" href="javascript:;">Показать дополнительные свойства &darr;</a> <br />
                    <div class="userdata hide">
                    <h3>Дополнительные поля</h3>';
        if (is_array($order['user_details'])) {
            foreach ($order['user_details'] as $name => $value) {
                $res.=$name . ': <br/><textarea name="Order[' . $order['id'] . '][user_details][' . $name . ']">' . $value . '</textarea><br/>';
            }
        }
        $res.='</div>
                </div>';


        $res .= '<div class="lc">
                   <div class="productsdata">
                   <h3>Состав заказа</h3>';
        if (is_array($order['products_details'])) {
            $res.='<table width="100%" cellpadding="0" cellspacing="0">
                     <tr><th>Наименование</th><th>Количество</th><th>Стоимость</th></tr>';
            foreach ($order['products_details'] as $prod) {
                $res .= '<tr class="bl">
                            <td>
                                <a href="' . $prod['url'] . '" target="_blank" class="outlink">
                                ' . $prod['pagetitle'] . ' ' . $prod['name'] . ' (' . $prod['article'] . ') 
                                </a>
                            </td>
                            <td> ' . $prod['count'] . '</td>
                            <td> ' . $prod['price'] . '</td>
                          </tr>';
            }
            $res.='<tr><td colspan="2" class="big">Итого </td><td class="big">' . $order['price'] . ' ' . $order['currency'] . '</td></tr>';
            $res.='</table>';
        }
        $res.='</div>
                 </div>';

        $res.='<div class="clear"></div>';        
        $res.='<input type="submit" value="Сохранить" class="button">';
        $res.='</form>';
        $res.='<a href="'.MShopController::getURL(array('id_order' =>'', 'action' => '')).'"> &larr; Назад</a>';
        return $res;
    }

    public function renderOption($vars, $value = false) {
        $option = $selected = '';
        foreach ($vars as $val => $name) {
            if ($val == $value)
                $selected = 'selected';
            $option.='<option value="' . $val . '" ' . $selected . '>' . $name . '</option>';
            $selected = '';
        }
        return $option;
    }

}

?>
