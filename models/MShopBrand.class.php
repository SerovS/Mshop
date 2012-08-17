<?php

/**
 * Class MShopBrand
 * Управление брендами. Таблица mshop_document
 * @author SerovAlexander <serov.sh@gmail.com>
 */
class MShopBrand {

    //put your code here
    public function __construct($modx, $model) {
        $this->modx = $modx;
        $this->model = $model;
    }

    public function getBrands() {
        $output = array();
        $sql = 'select * from ' . $this->modx->getFullTableName(MShopModel::CONTENT) . ' as o where o.template = \'' . $this->model->brand_template . '\'';
        $result = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($result)) {
            $output[$row['id']] = $row;
        }
        return $output;
    }

    public function saveBrand($id_brand, $id_content) {
        if (is_numeric($id_brand) && is_numeric($id_content) && $id_brand > 0 && $id_content > 0) {
            $sql = 'select count(*) as count from ' . $this->modx->getFullTableName(MShopModel::BRAND) . ' where id_content = \'' . $id_content . '\'';
            $result = $this->modx->db->query($sql);
            $row = $this->modx->db->getRow($result);            
            if ($row['count'] > 0)
                $this->update($id_brand, $id_content);
            else
                $this->insert($id_brand, $id_content);
        }
    }

    public function update($id_brand, $id_content) {
        $this->modx->db->update(array('id_brand' => $id_brand), $this->modx->getFullTableName(MShopModel::BRAND), 'id_content = "' . $id_content . '"');
    }

    public function insert($id_brand, $id_content) {
        $this->modx->db->insert(array('id_brand' => $id_brand, 'id_content' => $id_content), $this->modx->getFullTableName(MShopModel::BRAND));
    }

}

?>
