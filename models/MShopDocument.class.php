<?php

/**
 * Class MShopDocument
 * Работа с таблицей mshop_content
 * @author SerovAlexander <serov.sh@gmail.com>
 */
class MShopDocument {

    public $modx;

    public function __construct($modx, $model) {
        $this->modx = $modx;
        $this->model = $model;
    }

    /**
     * Возвращает масив документов. С постраничным разбиением.
     * @param type $id
     * @param int $parent
     * @param type $template
     * @param string $order
     * @param type $limit
     * @param type $start
     * @param type $tvs
     * @param type $group
     * @return type 
     */
    public function getDocuments($id = false, $parent = false, $template = false, $order = false, $limit = 30, $start = 0, $tvs = false, $group = false, $where = false) {


        $output = array();
        $left = $select = '';
        if ($where === false)
            $where = '';
        if (is_numeric($id))
            $where .= ' and content.id=\'' . $id . '\'';
        else {
            if (is_array($id))
                $where .= ' and content.id in ' . implode(',', $id) . '';
            if (is_numeric($parent))
                $where .= ' and content.parent=\'' . $parent . '\'';
            if (is_array($parent))
                $where .= ' and content.parent in (' . implode(',', $parent) . ')';
            if (is_array($template))
                $where .= ' and content.template in (' . implode(',', $template) . ')';
            if (is_numeric($template))
                $where .= ' and content.template in (' . intval($template) . ')';
        }
        if (is_array($tvs)) {
            foreach ($tvs as $tv) {
                if (is_numeric($tv) && $tv > 0) {
                    $select .= ', tv' . $tv . '.value as tv' . $tv . ' ';
                    $left .= 'left outer join ' . $this->modx->getFullTableName(MShopModel::TV) . ' as tv' . $tv . ' on (tv' . $tv . '.contentid = content.id and tv' . $tv . '.tmplvarid=' . $tv . ') ';
                }
            }
        }

        if (is_numeric($start) && $start >= 0 && is_numeric($limit))
            $lim = ' limit ' . intval($start) . ', ' . intval($limit) . '';

        if (isset($order) && $order)
            $order = ' order by ' . $this->modx->db->escape($order);

        if ($group) {
            $groupby = ' GROUP BY content.id ';
        }
        
        $sql = 'select *, brand.id_brand as id_brand, content.id as id, variant.id as id_variant, variant.price as price, variant.stock as stock, variant.name as variant_name, variant.article as article ' . $select . '
                  from (select * from ' . $this->modx->getFullTableName(MShopModel::CONTENT) . ' as content where 1 '.$where .$order. $lim.') as content
                  left outer join ' . $this->modx->getFullTableName(MShopModel::BRAND) . ' as brand on (content.id = brand.id_content)                  
                  left outer join ' . $this->modx->getFullTableName(MShopModel::VARIANT) . ' as variant on (content.id = variant.id_content) 
                      ' . $left . '
                  where 1=1  ' . $groupby ;
         //echo $sql;
        $result = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($result)) {
            $output[$row['id']] = $row;
            $output[$row['id']]['price'] = $this->model->variant->setPrice($row['price']);
            $variants[$row['id']][$row['id_variant']]['id_external'] = $row['id_external_variant'];
            $variants[$row['id']][$row['id_variant']]['name'] = $row['variant_name'];
            $variants[$row['id']][$row['id_variant']]['article'] = $row['article'];
            $variants[$row['id']][$row['id_variant']]['price'] = $this->model->variant->setPrice($row['price']);
            $variants[$row['id']][$row['id_variant']]['stock'] = $row['stock'];

            $output[$row['id']]['variants'] = $variants[$row['id']];
            $output[$row['id']]['url'] = $this->makeFrontUrl($row);
        }
        return $output;
    }

    public function getCount($id = false, $parent = false, $template = false) {

        $where = '';
        if (is_numeric($id))
            $where .= ' and content.id=\'' . $id . '\'';
        else {
            if (is_array($id))
                $where .= ' and content.id in ' . implode(',', $id) . '';
            if (is_numeric($parent))
                $where .= ' and content.parent=\'' . $parent . '\'';
            if (is_array($parent))
                $where .= ' and content.parent in (' . implode(',', $parent) . ')';
            if (is_array($template))
                $where .= ' and content.template in (' . implode(',', $template) . ')';
            if (is_numeric($template))
                $where .= ' and content.template in (' . intval($template) . ')';
        }

        $sql = 'select count(*) as count
                  from ' . $this->modx->getFullTableName(MShopModel::CONTENT) . ' as content                   
                  where 1=1 ' . $where . ' ';
        // echo $sql;        
        $result = $this->modx->db->query($sql);
        $row = $this->modx->db->getRow($result);
        return $row['count'];
    }

    public function getParents($doc, $parents = false, $i = 0) {
        $i++;
        $parent = $this->getDocuments($doc['parent']);
        $parent = $parent[$doc['parent']];
        $parents[$i] = $parent;
        if ($parent['parent'] == 0) {
            return $parents;
        } else {
            return $this->getParents($parent, $parents, $i);
        }
    }

    /**
     * Возвращает ID потомков документа
     * @param integer, array $id
     * @return array 
     */
    public function getChildsId($id) {
        if (is_array($id))
            $id = implode(',', $id);
        $sql = 'select id, pagetitle from ' . $this->modx->getFullTableName(MShopModel::CONTENT) . ' 
                            where parent in (' . $id . ')';
        $output = array();
        $result = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($result)) {
            //$output[$row['pagetitle']] = $row['id'];
            $output[$row['id']] = $row['id'];
        }
        return $output;
    }

    public function getProperties($id_content) {
        $output = array();
        $sql = 'select * from ' . $this->modx->getFullTableName(MShopModel::PROPERTIES) . ' where id_content = \'' . $id_content . '\'';
        //  echo $sql;
        $result = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($result)) {
            $output[$row['id_property']] = $row['value'];
        }

        return $output;
    }

    /**
     * Получаем массив всех каткгорий
     * @return <array> 
     */
    public function getCategories() {
        $output = array();
        $sql = 'select * from ' . $this->modx->getFullTableName(MShopModel::CONTENT) . ' as o where o.template = \'' . $this->model->category_template . '\'';
        //  echo $sql;

        $result = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($result)) {
            $output[$row['id']] = $row;
        }

        return $output;
    }

    /**
     * Получение всех данных по ID товара.
     * @param <integer> $id_content
     * @return <array> $output 
     */
    public function getDoc($id_content) {
        $output = array();
        $sql = 'select * from ' . $this->modx->getFullTableName(MShopModel::CONTENT) . 'as c where id=\'' . $id_content . '\'';
        echo $sql;
        $result = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($result)) {
            $output[$row['id']] = $row;
        }
        return $output;
    }

    /**
     * Получение всех вариантов цен.
     * @param <integer> $id_content
     * @return type 
     */
    public function getVariants($id_content) {
        $output = array();
        $sql = 'select * from ' . $this->modx->getFullTableName(MShopModel::VARIANT) . ' as o where id_content = \'' . $id_content . '\'';
        $result = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($result)) {
            $output[$row['id']] = $row;
        }
        return $output;
    }

    /**
     * Обновление существующего документа
     * @param <array> $fields Масив значений
     * @param <integer> $id ID документ
     * @return <array> масив значений документа
     */
    public function updateDocument($fields, $id) {
        $this->modx->db->update($this->checkData($fields), $this->modx->getFullTableName(MShopModel::CONTENT), 'id = "' . $id . '"');
        return $this->checkData($fields);
    }

    /**
     * Вставка нового документа
     * @param <array> $fields
     * @return integer ID нового документа 
     */
    public function insertDocument($fields) {
        $this->modx->db->insert($this->checkData($fields), $this->modx->getFullTableName(MShopModel::CONTENT));
        return $this->modx->db->getInsertId();
    }

    /**
     * Определение всех столбцов БД перед вставкой.
     * @param <array> $arr массив POST
     * @return array
     */
    public function checkData($arr) {
        // preprocess POST values

        if (isset($arr['pagetitle']))
            $res['pagetitle'] = $this->modx->db->escape($arr['pagetitle']);
        if (isset($arr['longtitle']))
            $res['longtitle'] = $this->modx->db->escape($arr['longtitle']);
        if (isset($arr['description']))
            $res['description'] = $this->modx->db->escape($arr['description']);
        if (isset($arr['alias']))
            $res['alias'] = $this->modx->db->escape($arr['alias']);
        if (isset($arr['published']))
            $res['published'] = $arr['published'];

        if (isset($arr['pub_date']))
            $res['pub_date'] = $arr['pub_date'];
        $res['parent'] = $arr['parent'] != '' ? $arr['parent'] : 0;
        if (isset($arr['isfolder']))
            $res['isfolder'] = $arr['isfolder'];
        if (isset($arr['introtext']))
            $res['introtext'] = $this->modx->db->escape($arr['introtext']);
        if (isset($arr['ta']))
            $res['content'] = $this->modx->db->escape($arr['ta']);
        if (isset($arr['template']))
            $res['template'] = $arr['template'];
        $res['menuindex'] = !empty($arr['menuindex']) ? $arr['menuindex'] : 0;
        if (isset($arr['menutitle']))
            $res['menutitle'] = $arr['menutitle'];
        if (isset($arr['hidemenu']))
            $res['hidemenu'] = intval($arr['hidemenu']);
        if (isset($arr['richtext']))
            $res['richtext'] = $arr['richtext'];
        $res['editedon'] = strtotime(date('d.m.Y H:i:s'));

        return $res;
    }

    public function remove($id) {
        if (is_numeric($id) && $id > 0) {
            $doc = $this->getDocuments($id);
            $this->modx->db->delete($this->modx->getFullTableName(MShopModel::CONTENT), 'id = "' . $id . '"');
            $this->modx->db->update(array('parent' => $doc['parent']), $this->modx->getFullTableName(MShopModel::CONTENT), 'parent = \'' . $id . '\'');
            $this->modx->db->delete($this->modx->getFullTableName(MShopModel::BRAND), 'id_content = "' . $id . '"');
            $this->modx->db->delete($this->modx->getFullTableName(MShopModel::PROPERTIES), 'id_content = "' . $id . '"');
            $this->modx->db->delete($this->modx->getFullTableName(MShopModel::VARIANT), 'id_content = "' . $id . '"');
            $this->modx->db->delete($this->modx->getFullTableName(MShopModel::TV), 'contentid = "' . $id . '"');
        }
    }

    public function saveTVs($tmplvars, $id_content) {
        foreach ($tmplvars as $field => $value) {
            $tvId = $value[0];
            $tvVal = $this->modx->db->escape($value[1]);
            if (!empty($tvVal) && is_numeric($tvId) && $tvId > 0 && is_numeric($id_content) && $id_content > 0) {
                $recordCount = $this->modx->db->getValue($this->modx->db->select("count(*)", $this->modx->getFullTableName(MShopModel::TV), 'contentid = \'' . $id_content . '\' AND tmplvarid = \'' . $tvId . '\''));
                if ($recordCount > 0) {
                    $rs = $this->modx->db->update(array('value' => $tvVal), $this->modx->getFullTableName(MShopModel::TV), 'contentid = \'' . $id_content . '\' AND tmplvarid = \'' . $tvId . '\'');
                } else {
                    $rs = $this->modx->db->insert(array('tmplvarid' => $tvId, 'contentid' => $id_content, 'value' => $tvVal), $this->modx->getFullTableName(MShopModel::TV));
                }
            }
        }
    }

    public function isBrand($doc) {
        if ($doc['template'] == $this->model->brand_template)
            return true;
        if (is_array($this->model->brand_template) && in_array($doc['template'], $this->model->brand_template))
            return true;
        return false;
    }

    public function isCategory($doc) {
        if ($doc['template'] == $this->model->category_template)
            return true;
        if (is_array($this->model->category_template) && in_array($doc['template'], $this->model->category_template))
            return true;
        return false;
    }

    public function isProduct($doc) {
        if ($doc['template'] == $this->model->product_template)
            return true;
        if (is_array($this->model->product_template) && in_array($doc['template'], $this->model->product_template))
            return true;
        return false;
    }

    public function makeProductUrl($id_content) {
        return $this->model->url_catalog . '/' . $id_content;
    }

    public function makeCategoryUrl($id_content) {
        return $this->model->url_catalog . '/' . $id_content;
    }

    public function makeBrandUrl($id_content) {
        return $this->model->url_catalog . '/' . $id_content;
    }

    /**
     * Формируем url для сайта (front)
     * @param array $doc - документ
     * @return string 
     */
    public function makeFrontUrl($doc) {

        $link = $doc['id'];
        if (!empty($doc['alias']))
            $link = $doc['alias'];


        if ($this->isProduct($doc))
            return $this->makeProductUrl($link);
        if ($this->isCategory($doc))
            return $this->makeCategoryUrl($link);
        if ($this->isBrand($doc))
            return $this->makeBrandUrl($link);
    }

    /**
     * Получаем код шаблона по его ID
     * @return type
     * @throws Exception 
     */
    public function getTemplate($id) {
        $sql = "SELECT `content` FROM " . $this->modx->getFullTableName("site_templates") . " WHERE " . $this->modx->getFullTableName("site_templates") . ".`id` = '" . $id . "';";
        $result = $this->modx->db->query($sql);
        $rowCount = $this->modx->db->getRecordCount($result);
        if ($rowCount > 1) {
            throw new Exception('Incorrect number of templates returned from database', 1); //lang            
        } elseif ($rowCount == 1) {
            $row = $this->modx->db->getRow($result);
            return $row['content'];
        }
    }

    /**
     * Получаем TV параметры для одного документа
     * @param <array> $doc Документ
     * @return array
     */
    public function getTVs($doc) {
        $tmplvars = array();
        $sql = "SELECT tv.*, IF(tvc.value!='',tvc.value,tv.default_text) as value ";
        $sql .= "FROM " . $this->modx->getFullTableName("site_tmplvars") . " tv ";
        $sql .= "INNER JOIN " . $this->modx->getFullTableName("site_tmplvar_templates") . " tvtpl ON tvtpl.tmplvarid = tv.id ";
        $sql .= "LEFT JOIN " . $this->modx->getFullTableName(MShopModel::TV) . " tvc ON tvc.tmplvarid=tv.id AND tvc.contentid = '" . $doc['id'] . "' ";
        $sql .= "WHERE tvtpl.templateid = '" . $doc['template'] . "'";
        $rs = $this->modx->db->query($sql);
        $rowCount = $this->modx->db->getRecordCount($rs);
        if ($rowCount > 0) {
            for ($i = 0; $i < $rowCount; $i++) {
                $row = $this->modx->db->getRow($rs);
                $tmplvars[$row['name']] = array(
                    $row['name'],
                    $row['value'],
                    $row['display'],
                    $row['display_params'],
                    $row['type']
                );
            }
        }
        return $tmplvars;
    }

    /**
     * Формируем постраничное переключение для фронта
     * @param type $count
     * @param type $limit
     * @param type $current
     * @param type $prefix
     * @param type $current_url
     * @return string 
     */
    public function getFrontPager($count, $limit, $current = false, $prefix = '', $current_url = '') {
        if ($count / $limit > floor($count / $limit)) {
            $pages = $count / $limit + 1;
        } else {
            $pages = $count / $limit;
        }
        $res = $class = '';
//        $docs = $this->getDocuments($this->model->current_page);
//        $current_doc = current($docs);
        for ($i = 1; $i <= $pages; $i++) {
            $start = ($i * $limit) - $limit;
            $url = $current_url . '?' . http_build_query(array($prefix . '_start' => $start), '', '&amp;');
            if ($current == $start)
                $res .= '<span>' . $i . '</span>';
            else
                $res .= '<a href="' . $url . '" class="pager ' . $class . '">' . $i . '</a>';
            $class = '';
        }
        return $res;
    }

    /**
     * Получаем ID документа по его внешнего ключу.
     * @param type $id_external Ключ из 1с
     * @return integer $id_content 
     */
    public function getExternalDocumentId($id_external) {
        $sql = 'select id_content from ' . $this->modx->getFullTableName(MShopModel::EXTERNAL) . ' as extarnal 
                  where extarnal.id_external = \'' . $this->modx->db->escape($id_external) . '\' ';
        $result = $this->modx->db->query($sql);
        if ($result) {
            $row = $this->modx->db->getRow($result);
            return $row['id_content'];
        }
        return false;
    }

    /**
     * Поиск по товарам.
     * @param type $str
     * @param type $escape
     * @return type 
     */
    public function search($str, $escape = true) {
        if ($escape)
            $str = mysql_real_escape_string($str);
        $res = array();
        $left = $select = '';

        $select.='select content.id, 
            content.template, 
            content.pagetitle, 
            content.longtitle, 
            content.description, 
            content.content, 
            content.introtext, 
            content.alias, 
            content.menutitle, 
            variant.price as price, 
            variant.stock as stock';

        if (empty($tvs)) {
            $tvs = array(1);
        }
        if (is_array($tvs)) {
            foreach ($tvs as $tv) {
                if (is_numeric($tv) && $tv > 0) {
                    $select .= ', (IFNULL(tv' . $tv . '.value, \'/assets/images/common/nophoto.png\')) as tv' . $tv . ' ';
                    $left .= 'left outer join ' . $this->modx->getFullTableName(MShopModel::TV) . ' as tv' . $tv . ' on (content.id = tv' . $tv . '.contentid and tv' . $tv . '.tmplvarid=' . $tv . ') ';
                }
            }
        }
        $sql2 = '';
        /* SQL Evelin Gerbovnik +return stock, price and $tvs */
        $sql2.=$select . 'from' . $this->modx->getFullTableName(MShopModel::CONTENT) . ' as content 
		 				left outer join ' . $this->modx->getFullTableName(MShopModel::VARIANT) . ' as variant on (content.id = variant.id_content)' .
                $left
                . 'where 
						content.pagetitle like (\'%' . $str . '%\') OR
                  		content.longtitle like (\'%' . $str . '%\') OR
                  		content.description like (\'%' . $str . '%\') OR
                  		content.content like (\'%' . $str . '%\') OR
                  		content.introtext like (\'%' . $str . '%\') OR
                  		content.menutitle like (\'%' . $str . '%\') OR
                  		content.alias like (\'%' . $str . '%\')';
        //print_r($sql2);
        $result = $this->modx->db->query($sql2);
        if ($result) {
            while ($row = $this->modx->db->getRow($result)) {
                $res['mshop' . $row['id']] = $row;
                $res['mshop' . $row['id']]['url'] = $this->makeFrontUrl($row);
            }
        }
        //print_r($res);
        return $res;
    }

    public function getIdDocumentByAlias($alias) {
        $alias = $this->modx->db->escape($alias);
        $sql = 'select id from ' . $this->modx->getFullTableName(MShopModel::CONTENT) . ' 
                  where alias = \'' . $alias . '\'';
        //echo $sql;
        $output = array();
        $result = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($result)) {
            return $row['id'];
        }
        return false;
    }

    public function getTvParams($ids = false) {
        $sql = 'select *, tvn.name as name from ' . $this->modx->getFullTableName(MShopModel::TV) . ' as tv
                 left join ' . $this->modx->getFullTableName('site_tmplvars') . ' as tvn on (tvn.id = tv.tmplvarid)
                  ';
        //echo $sql;
        $res = array();
        $result = $this->modx->db->query($sql);
        while ($row = $this->modx->db->getRow($result)) {
            $res[$row['contentid']][$row['name']] = $row['value'];
        }
        return $res;
    }

}

?>