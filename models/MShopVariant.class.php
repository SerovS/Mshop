<?php

/**
 * Модель для работы с вариантами товара. 
 * Таблица mshop_variant
 * @author Serov Alexander <serov.sh@gmail.com>
 */
class MShopVariant {

    public $id;
    public $name;
    public $article;
    public $price;
    public $stock;
    public $position;
    public $modx;
    public $_group;

    public function __construct($modx, $model) {
        $this->modx = $modx;
        $this->model = $model;
    }

    /**
     * Удаление вариантов.
     * @param <string> $vars  ID вариантов для удаления через запятую
     */
    public function removeVariants($vars) {
        $ids = explode(",", $vars);
        foreach ($ids as $id) {
            if (is_numeric($id) && $id > 0) {
                $this->modx->db->delete($this->modx->getFullTableName(MShopModel::VARIANT), 'id = "' . $id . '"');
            }
        }
    }

    /**
     * Сохраняет варианты цен при сохранение товара.
     * @param <array> $variants Атрибуты варианта
     * @param <integer> $id_content ID документа(товара) для которого необходимо сохранить вариант
     */
    public function saveVariants($variants, $id_content) {
        foreach ($variants as $id_variant => $variant_data) {
            if (is_numeric($id_variant) && $id_variant > 0)
                $this->update($this->checkData($variant_data, $id_content), $id_variant);
            elseif (is_numeric($variant_data['price']) && $variant_data['price'] > 0)
                $this->insert($this->checkData($variant_data, $id_content));
        }
    }

    /**
     * Обновляем вариант
     * @param <array> $data
     * @param <integer> $id_variant
     * @return <array> 
     */
    public function update($data, $id_variant) {
        $this->modx->db->update($data, $this->modx->getFullTableName(MShopModel::VARIANT), 'id = "' . $id_variant . '"');
        return $data;
    }

    /**
     * Вставляем новый вариант
     * @param <array> $data
     * @return <integer> 
     */
    public function insert($data) {
        $this->modx->db->insert($data, $this->modx->getFullTableName(MShopModel::VARIANT));
        return $this->modx->db->getInsertId();
    }

    /**
     * Подготовка данных перед отправкой в БД.
     * @param <array> $arr
     * @param <integer> $id_content
     * @return <array> 
     */
    public function checkData($arr, $id_content) {
        // preprocess POST values
        $res['id_content'] = $id_content;
        $res['price'] = $this->modx->db->escape($arr['price']);
        $res['article'] = $this->modx->db->escape($arr['article']);
        $res['name'] = isset($arr['name']) ? $this->modx->db->escape($arr['name']) : ''; //документ имя
        $res['stock'] = $this->modx->db->escape($arr['stock']);
        $res['position'] = isset($arr['position']) ? $arr['position'] : 1;
        $res['unit'] = isset($arr['unit']) ? $arr['unit'] : 'шт';
        if (isset($arr['id_external']))
            $res['id_external'] = $arr['id_external'];
        return $res;
    }

    public function getDocuments($id = false) {
        $output = array();
        $where = '';
        if (is_numeric($id))
            $where .= ' and variant.id=\'' . $id . '\'';
        if (is_array($id) && !empty($id))
            $where .= ' and variant.id in (' . implode(',', $id) . ')';

        $sql = 'select *, variant.id as id_variant, brand.id_brand as id_brand, content.id as id, content.pagetitle as pagetitle
                  from ' . $this->modx->getFullTableName(MShopModel::VARIANT) . ' as variant 
                  left outer join ' . $this->modx->getFullTableName(MShopModel::CONTENT) . ' as content on (content.id = variant.id_content) 
                  left outer join ' . $this->modx->getFullTableName(MShopModel::BRAND) . ' as brand on (content.id = brand.id_content)                                    
                  where 1=1 ' . $where;
        //echo $sql;
        $result = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($result)) {
            $output[$row['id_variant']] = $row;
            $output[$row['id_variant']]['price'] = $this->setPrice($row['price']);
            $output[$row['id_variant']]['url'] = $this->model->document->makeFrontUrl($row);
        }

        return $output;
    }

    /**
     * Получить вариант по внешнему ключу (из 1с)
     * @param type $id_external
     * @return type 
     */
    public function getExternalVariant($id_external) {
        $id_external = intval($id_external);
        $output = array();
        $where = '';
        if (is_numeric($id_external) && $id_external > 0) {
            $where .= ' and variant.id_external=\'' . intval($id_external) . '\'';

            $sql = 'select *
                  from ' . $this->modx->getFullTableName(MShopModel::VARIANT) . ' as variant                   
                  where 1=1 ' . $where;
            //echo $sql;
            $result = $this->modx->db->query($sql);
            while ($row = $this->modx->db->getRow($result)) {
                $output = $row;
            }
        }
        return $output;
    }

    public function setPrice($price) {

        $userLoggedIn = $this->modx->userLoggedIn();
//print_r($this->model->getConfig());
        if (isset($userLoggedIn) && $userLoggedIn['id'] > 0) {
            $this->getWebGroup($userLoggedIn['id']);
            $n = 'discount_' . $this->_group['id_group'];
            $price = round(($price - ($price * $this->model->$n * 0.01)), 2);
        }


        return $price;
    }

    /**
     * Получаем группу веб пользователя.
     * @param integer                $id_user
     * @return array 
     */
    public function getWebGroup($id_user) {
        if (empty($this->_group) && !isset($this->modx->web_group)) {
            $sql = "SELECT wg.webuser as id_user, wg.webgroup as id_group, gr.name as group_name FROM " . $this->modx->getFullTableName("web_groups") . " as wg
            LEFT JOIN " . $this->modx->getFullTableName("webgroup_names") . " as gr on (gr.id = wg.webgroup) 
            where webuser='" . intval($id_user) . "'";

            $res = $this->modx->db->query($sql);
            $this->_group = $this->modx->db->getRow($res);
            $this->modx->web_group = $this->_group;
        }
        if (isset($this->modx->web_group)) {

            $this->_group = $this->modx->web_group;
        }
        return $this->_group;
    }

    /**
     * Получаем все веб группы пользователей.
     * @return array 
     */
    static function getWebGroups($modx) {
        $res = array();
        $sql = "SELECT * FROM " . $modx->getFullTableName("webgroup_names") . "";
        $result = $modx->db->query($sql);
        while ($row = $modx->db->getRow($result)) {
            $res[$row['id']] = $row;
        }
        return $res;
    }

    /**
     * Функция пакетного обновления вариантов.
     *  $arr - массив изменяемых значений, формате id документа, значение
     *  $name - имя изменяемого значения
     * */
    public function updatevars($arr, $name) {
        foreach ($arr as $id => $value) {
            $fields[$name] = $value;
            $this->update($fields, $id);
        }
    }

}

?>
