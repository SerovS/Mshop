<?php

/**
 * Модель для работы с заказами. 
 * Таблица mshop_order
 * @author Serov Alexander <serov.sh@gmail.com>
 */
class MShopOrder {

    public $id;
    public $name;
    public $article;
    public $price;
    public $stock;
    public $position;
    public $modx;
    public $status_name = array(1 => 'Новый', 10 => 'Принят к оплате', 15 => 'Оплачен', 20 => 'Отправлен', 30 => 'Выполнен', 50 => 'Отменен');

    public function __construct($modx, $model) {
        $this->modx = $modx;
        $this->model = $model;
    }

    /**
     * Сохраняем новый заказ
     * @param <array> $variants Атрибуты варианта
     * @param <integer> $id_content ID документа(товара) для которого необходимо сохранить вариант
     */
    public function newOrder(&$fields) {
        //print_r($fields);
        $data = $this->getNewOrderData($fields);
        $id_order = $this->insert($this->checkData($data));
        $fields = array_merge($fields, $this->getEmailFieds($id_order));
        $this->sendUserEmail($fields, 'Новый заказ');
        $this->model->emptyCart();
    }

    /**
     * Сохранение заказа из модуля
     * @param type $orders 
     */
    public function saveOrders($orders) {
        if (is_array($orders)) {
            foreach ($orders as $id_order => $data) {
                $new_status = false;
                if (isset($data['user_details']) && is_array($data['user_details']))
                    $data['user_details'] = serialize($data['user_details']);
                if ($data['last_status'] != $data['status'])
                    $new_status = true;
                $this->update($this->checkData($data), $id_order);
                if ($new_status)
                    $this->changeStatus($id_order);
            }
        }else
            throw new Exception('Неправильный формат отправки данных', 1); //lang   
//$this->sendUserEmail($fields, 'Новый заказ');
    }

    public function changeStatus($id_order) {
        $fields = $this->getEmailFieds($id_order);
        $subject = 'Изменение статуса заказа №' . $id_order;
        $message = 'Вашему заказу присвоен статус:' . $fields['mshop_order_status_name'];
        $this->sendUserEmail($fields, $subject, $message);
    }

    /**
     * Получаем поля заказа для отправки email
     * @param <integer> $id_order
     * @return array 
     */
    public function getEmailFieds($id_order) {
        $fields = array();
        $order = $this->getOrder($id_order);

        foreach ($order as $name => $param) {
            $fields['mshop_order_' . $name] = $param;
        }
        if (is_array($order['products_details']) && !empty($order['products_details'])) {
            $str = '<table>
            <tr><td>Наименование</td><td>Цена</td><td>Количество</td></tr>';

            foreach ($order['products_details'] as $product) {
                $str.='<tr>
                <td>' . $product['pagetitle'] . '</td>
                <td>' . $product['price'] . '</td>
                <td>' . $product['count'] . '</td>
                </tr>';
            }
            $str.='</table>';
            $fields['mshop_order_products'] = $str;
        }else
            throw new Exception('Корзина пуста', 0); //lang         
        if (is_array($order['user_details'])) {
            foreach ($order['user_details'] as $name => $param) {
                $fields['mshop_order_user_' . $name] = $param;
            }
        }

        $fields['mshop_order_status_name'] = $this->status_name[$order['status']];

        return $fields;
    }

    /**
     * Отправляем сообщение заказчику.
     * @param <array> $fields Поля заказа для email
     * @param <string> $subject Тема email
     * @param <string> $message  Сообщение
     */
    public function sendUserEmail($fields, $subject, $message = false) {
        if ($message != false)
            $fields['mshop_message'] = $message;
        $config['site_url'] = $this->modx->config['site_url'];
        $config['site_name'] = $this->modx->config['site_name'];

        $fields = array_merge($fields, $config);
        $body = $this->modx->parseChunk($this->model->user_email_tpl, $fields, '[+', '+]');
        $body = str_replace('[+mshop_message+]', '', $body);
        $email = $fields['mshop_order_email'];
        $this->model->sendMail($subject, $email, $body);
    }

    /**
     * Получаем заказ по ID
     * @param <integer> $id
     * @return array 
     */
    public function getOrder($id) {
        $sql = 'select * from ' . $this->modx->getFullTableName(MShopModel::ORDER) . ' as ord where ord.id = \'' . $id . '\'';
        $result = $this->modx->db->query($sql);
        $order = $this->modx->db->getRow($result);
        $order['user_details'] = unserialize($order['user_details']);
        $order['products_details'] = unserialize($order['products_details']);
        $order['url'] = $this->makeFrontUrl($order);
        return $order;
    }

    /**
     * Получаем список заказов.
     * @param type $ids
     * @param type $start
     * @param type $limit
     * @return string 
     */
    public function getOrders($ids = false, $start = 0, $limit = 30) {
        $orders = array();
        if (is_array($ids))
            $where .= ' and ord.id in (' . implode(",", $ids) . ')';
        if (is_numeric($start) && $start >= 0 && is_numeric($limit))
            $lim = 'limit ' . intval($start) . ', ' . intval($limit) . '';
        $sql = 'select * from ' . $this->modx->getFullTableName(MShopModel::ORDER) . ' as ord where 1=1 ' . $where . ' order by create_date DESC ' . $lim;
        $result = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($result)) {
            $orders[$row['id']] = $row;
            $orders[$row['id']]['status_name'] = $this->status_name[$row['status']];
            $orders[$row['id']]['user_details'] = unserialize($row['user_details']);
            $orders[$row['id']]['products_details'] = unserialize($row['products_details']);
            $orders[$row['id']]['url'] = $this->makeFrontUrl($row);
        }
        return $orders;
    }

    /**
     * Получаем количество всех заказов. Для страниц.
     * @return type 
     */
    public function getCountOrders() {
        $sql = 'select count(*) as count from ' . $this->modx->getFullTableName(MShopModel::ORDER) . ' as ord where 1=1';
        $result = $this->modx->db->query($sql);
        $row = $this->modx->db->getRow($result);
        return $row['count'];
    }

    /**
     * Обновляем вариант
     * @param <array> $data
     * @param <integer> $id_order
     * @return <array> 
     */
    public function update($data, $id_order) {
        $this->modx->db->update($data, $this->modx->getFullTableName(MShopModel::ORDER), 'id = "' . $id_order . '"');
        return $data;
    }

    /**
     * Вставляем новый
     * @param <array> $data
     * @return <integer> 
     */
    public function insert($data) {
        $this->modx->db->insert($data, $this->modx->getFullTableName(MShopModel::ORDER));
        return $this->modx->db->getInsertId();
    }

    public function removeOrder($id) {
        $this->modx->db->delete($this->modx->getFullTableName(MShopModel::ORDER), 'id =\'' . $id . '\'');
        return true;
    }

    /**
     * Подготовка данных перед отправкой в БД.
     * @param <array> $arr
     * @param <integer> $id_content
     * @return <array> 
     */
    public function checkData($arr) {
        $res = array();
        foreach ($arr as $name => $value) {
            if (!is_array($value))
                $res[$name] = $this->modx->db->escape($value);
        }
        unset($res['url']);
        unset($res['last_status']);
        return $res;
    }

    /**
     * Получаем данные для нового заказа. Инициализация нового заказа. 
     */
    public function getNewOrderData($fields) {
        $data = array();
        $cart = $this->model->getCartInfo();
        $data['products_details'] = serialize($cart['products']);
        $data['price'] = $cart['total'];
        $data['email'] = $fields['email'];
        $data['name'] = $fields['name'];
        $data['phone'] = $fields['phone'];
        $data['comment'] = $fields['comment'];
        $data['address'] = $fields['address'];
        $data['status'] = 1;
        $userLoggedIn = $this->modx->userLoggedIn();
        $data['id_user'] = $userLoggedIn !== false ? $userLoggedIn['id'] : 0;
        $user_details = array();
        if (isset($fields['mshop_fields'])) {
            $ud = explode(',', $fields['mshop_fields']);
            foreach ($ud as $name) {
                if (isset($_POST[$name]))
                    $user_details[$name] = $_POST[$name];
            }
        }
        $data['user_details'] = serialize($user_details);
        $data['access_key'] = md5(date('H:i:s d Y s i') . $fields['email'] . $fields['name']);
        $data['create_date'] = date('Y-m-d H:i:s');
        $usrdata = $this->modx->getUserData();
        $data['ip'] = $usrdata['ip'];
        $data['currency'] = 'руб';
        return $data;
    }

    /**
     * Возвращает ссылку на заказ для сайта.
     * @param array $order Заказ
     * @return string 
     */
    public function makeFrontUrl($order) {
        return $this->modx->config['site_url'] . 'order/?id_order=' . $order['id'] . '&access_key=' . $order['access_key'];
    }

    /**
     * Оплата заказа. Изменение статуса заказа.
     * 
     * @param type $order 
     */
    public function payOrder($order) {
        $order['last_status'] = $order['status'];
        $order['status'] = 15;
        $this->saveOrders(array($order['id'] => $order));
    }

}

?>
