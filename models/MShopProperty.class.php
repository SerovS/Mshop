<?php
/**
 * Класс отвечающий за свойства товаров. 
 * Сохранение свойства - как объекта;
 * Связь свойства с категориями продукции;
 * Cохранение свойств(а) продукта.
 * Таблицы: prefix_mshop_property, prefix_mshop_properties, prefix_mshop_properties2cat;
 * @author Serov Alexander <serov.sh@gmail.com>
 * @version
 * @package MShop
 * @since 1.0
 * 
 */
Class MShopProperty {

    public function __construct($modx, $model) {
        $this->modx = $modx;
        $this->model = $model;
    }

    /**
     * Получить свойства для категории
     * @param integer $id_cat
     * @return array 
     */
    public function getProperties($id_cat = false) {
        $where = '';
        $output = array();
        if ($id_cat && is_numeric($id_cat)) {
            $where .= ' and p2cat.id_content = \'' . $id_cat . '\'';
        }
        $sql = 'select p.id as id, p.options as options, p.name as name, content.id as id_category, content.pagetitle as category 
                 from ' . $this->modx->getFullTableName(MShopModel::PROPERTY) . ' as p
                 left outer join ' . $this->modx->getFullTableName(MShopModel::PROPERTIES2CAT) . ' as p2cat on (p.id = p2cat.id_property)
                 left outer join ' . $this->modx->getFullTableName(MShopModel::CONTENT) . ' as content on (p2cat.id_content =content.id)
                     where 1=1 ' . $where;
        //echo $sql;
        $result = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($result)) {
            if (!empty($row['options']))
                $row['options'] = unserialize($row['options']);
            $output[$row['id']]['property'] = $row;
            $output[$row['id']]['categories'][$row['id_category']] = $row['category'];
        }
        return $output;
    }

    /**
     * Возвращает одно свойство по ID или пустое (новое) свойство.
     * @param type $id_property
     * @return type 
     */
    public function getProperty($id_property) {
        if (is_numeric($id_property) && $id_property > 0) {
            return $this->select($id_property);
        } else {
            return $this->emptyProperty();
        }
    }

    /**
     * Сохранение свойств(а) для товара
     * @param type $properties
     * @param type $id_content 
     */
    public function saveProperties($properties = array(), $id_content = 0) {
        $this->modx->db->delete($this->modx->getFullTableName(MShopModel::PROPERTIES), 'id_content = "' . $id_content . '"');
        foreach ($properties as $id_property => $value) {
            if (is_numeric($id_property) && $id_property > 0) {
                $this->modx->db->insert(array('id_content' => $id_content, 'id_property' => $id_property, 'value' => $this->modx->db->escape($value)), $this->modx->getFullTableName(MShopModel::PROPERTIES));
            }
        }
    }

    /**
     * Сохранение свойства
     * @param type $arr 
     */
    public function saveProperty($arr) {

        if (is_numeric($arr['id']) && $arr['id'] > 0) {
            $id_property = $this->update($this->checkData($arr));
        } else {
            $id_property = $this->insert($this->checkData($arr));
        }
        if (is_array($arr['categories'])) {
            $this->saveProperty2Cat($arr['categories'], $id_property); 
        }
    }

    /**
     * Сохранение связи свойства с категориями
     * @param array $cats массив категорий
     * @param integer $id_property ID свойства
     */
    public function saveProperty2Cat($cats, $id_property) {
        $this->modx->db->delete($this->modx->getFullTableName(MShopModel::PROPERTIES2CAT), 'id_property = "' . $id_property . '"');
        foreach ($cats as $cat) {
            if (is_numeric($cat) && $cat > 0) {
                $this->modx->db->insert(array('id_content' => $cat, 'id_property' => $id_property), $this->modx->getFullTableName(MShopModel::PROPERTIES2CAT));
            }
        }
    }

    /**
     * Формирует массив для вставки в БД.
     * @param array $arr Массив сходный данных (POST)
     * @return array
     */
    public function checkData($arr) {
        if (is_numeric($arr['id']))
            $res['id'] = $arr['id'];
        $res['name'] = isset($arr['name']) ? $this->modx->db->escape($arr['name']) : ''; //документ им
        if (isset($arr['options']) && !empty($arr['options'])) {
            $res['options'] = serialize(explode("\r\n", $arr['options']));
        }
        $res['in_product'] = $arr['in_product'];
        $res['in_filter'] = $arr['in_filter'];
        $res['in_compare'] = $arr['in_compare'];
        $res['enabled'] = isset($arr['enabled']) ? $arr['enabled'] : 1;
        return $res;
    }

    /**
     * Вставка свойства в БД
     * @param array $data
     * @return integer 
     */
    public function insert($data) {
        $this->modx->db->insert($data, $this->modx->getFullTableName(MShopModel::PROPERTY));
        return $this->modx->db->getInsertId();
    }

    /**
     * Обновление свойства в БД
     * @param array $data
     * @return integer 
     */
    public function update($data) {
        $this->modx->db->update($data, $this->modx->getFullTableName(MShopModel::PROPERTY), 'id = "' . $data['id'] . '"');
        return $data['id'];
    }

    /**
     * Выборка одного свойства из БД и связанных с ним категорий.
     * @param integer $id
     * @return array 
     */
    public function select($id) {
        $sql = 'select * from ' . $this->modx->getFullTableName(MShopModel::PROPERTY) . ' where id = \'' . $id . '\'';
        $result = $this->modx->db->query($sql);
        $res = $this->modx->db->getRow($result);
        if (!empty($res['options']))
            $res['options'] = unserialize($res['options']);

        $sql = 'select id_content from ' . $this->modx->getFullTableName(MShopModel::PROPERTIES2CAT) . ' where id_property = \'' . $id . '\'';
        $result = $this->modx->db->query($sql);
        $cats = array();
        while ($row = $this->modx->db->getRow($result)) {
            $cats[$row['id_content']] = $row['id_content'];
        }
        $res['categories'] = $cats;
        return $res;
    }

    /**
     * Удаление свойства, связи свойства с категориями, значений свойства для продукции
     * @param integer $id
     * @return boolean 
     */
    public function removeProperty($id) {
        if (is_numeric($id) && $id > 0) {
            $this->modx->db->delete($this->modx->getFullTableName(MShopModel::PROPERTY), 'id = "' . $id . '"');
            $this->modx->db->delete($this->modx->getFullTableName(MShopModel::PROPERTIES), 'id_property = "' . $id . '"');
            $this->modx->db->delete($this->modx->getFullTableName(MShopModel::PROPERTIES2CAT), 'id_property = "' . $id . '"');
            return true;
        }
        return false;
    }

    /**
     * Возвращает массив пустого (нового) свойства
     * @return array 
     */
    public function emptyProperty() {
        return array('name' => 'Новое свойство', 'options' => '', 'id' => 'new', 'categories' => array());
    }

}

?>
