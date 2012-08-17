<?php

/**
 * Модель для работы с 1с. CommerceML v2
 * Таблица mshop_external_ids
 * @author Serov Alexander <serov.sh@gmail.com>
 */
class MShopExternal {

    public $model;
    public $modx;
    public $xml;
    public $offers;

    public function __construct($modx, $model) {
        $this->modx = $modx;
        $this->model = $model;
        $xml = @simplexml_load_file(dirname(__FILE__) . '/../1c/import.xml');
        $offers = @simplexml_load_file(dirname(__FILE__) . '/../1c/offers.xml');
        if (isset($xml))
            $this->xml = $xml;
        else
            throw new Exception('Неопределен import.xml', 0); //lang

        if (isset($offers))
            $this->offers = $offers;
        else
            throw new Exception('Неопределен offers.xml', 0); //lang
    }

    /**
     * Импорт категорий из каталога 1с
     * @param type $xml
     * @param type $parent
     * @param type $res
     * @return type 
     */
    public function importCategories($xml, $parent = 0, $res = false) {
        if ($res === false)
            $res = array();
        if (isset($xml->Группы->Группа)) {
            foreach ($xml->Группы->Группа as $xml_group) {
                //echo '1<br/>';
                $p = array('parent_id' => $parent_id, 'external_id' => $xml_group->Ид, 'name' => $xml_group->Наименование);
                $id_content = $this->model->document->getExternalDocumentId($xml_group->Ид);
                $fields = array(
                    'parent' => $parent,
                    'pagetitle' => $xml_group->Наименование,
                    'published' => 1,
                    'template' => $this->model->category_template);
                if ($id_content && is_numeric($id_content)) {
                    $this->model->document->updateDocument($fields, $id_content);
                    $res[$id_content] = 'Обновление ID:' . $id_content . ' название: ' . $xml_group->Наименование . ' непроизведено Ид:' . $xml_group->Ид;
                } else {
                    $id_content = $this->model->document->insertDocument($fields);
                    $this->insertExternalId($id_content, $xml_group->Ид);
                    $res[$id_content] = 'Создан новый документ ID:' . $id_content . ' название: ' . $xml_group->Наименование . ' Ид:' . $xml_group->Ид;
                }

                //   $_SESSION['categories_mapping'][strval($xml_group->Ид)] = $category_id;

                if (isset($xml_group->Группы->Группа))
                    $res = $this->importCategories($xml_group, $id_content, $res);
            }
            return $res;
        }
    }

    /**
     * Импорт продуктов из каталога 1с
     * @return string
     * @throws Exception 
     */
    public function importProducts() {
        $res = array();
        // Товары
        $i = 0;
        if (isset($this->xml->Каталог->Товары->Товар)) {
            foreach ($this->xml->Каталог->Товары->Товар as $xml_product) {
                $i++;
                $variant = $this->model->variant->getExternalVariant($xml_product->Ид);

                $variant = array_merge($variant, array(
                    'article' => $xml_product->Артикул,
                    'name' => $xml_product->Наименование,
                    'id_external' => $xml_product->Ид
                        ));

                $content = array(
                    'published' => 1,
                    'pagetitle' => $xml_product->Наименование,
                    'parent' => $this->model->document->getExternalDocumentId($xml_product->Группы->Ид),
                    'template' => $this->model->product_template
                );
                //групируем по имени товары
                $add[$i]['tovar'] = $content;
                $add[$i]['variants'][] = $variant;
            }
            //print_r($add);
            $id_content = 0;
            foreach ($add as $product) {
                foreach ($product['variants'] as $variant) {
                    if (!empty($variant) && is_numeric($variant['id_content'])) {
                        $id_content = $variant['id_content'];
                        $this->model->document->updateDocument($product['tovar'], $id_content);
                        $res[] = 'Обновлен документ ID:' . $id_content . ' ' . $f['name'] . ' Ид:' . $xml_product->Ид;
                        $this->model->variant->update($this->model->variant->checkData($variant, $id_content), $id_variant);
                        $res[] = 'Обновление варианта  ID:' . $variant['name'] . '(' . $variant['id'] . ') ' . $product['tovar']['pagetitle'] . ' Ид:' . $variant['id_external'];
                    } else {
                        if ($id_content > 0) {
                            $this->model->document->updateDocument($product['tovar'], $id_content);
                            $res[] = 'Обновлен документ ID:' . $id_content . ' ' . $product['tovar']['pagetitle'] . ' Ид:' . $variant['id_external'];
                        } else {
                            $id_content = $this->model->document->insertDocument($product['tovar']);
                            $res[] = 'Создан новый документ ID:' . $id_content . ' ' . $product['tovar']['pagetitle'] . ' Ид:' . $variant['id_external'];
                        }
                        $this->model->variant->insert($this->model->variant->checkData($variant, $id_content));
                        $res[] = 'Создан новый вариант ' . $variant['name'] . ' для документа ID:' . $id_content . ' Ид:' . $variant['id_external'];
                    }
                }
                $id_content = 0;
            }


            return $res;
        }
        throw new Exception('Неопределен $this->xml->Каталог->Товары->Товар', 0); //lang
    }

    /**
     * Импорт цен из каталога 1с
     * @return string 
     */
    public function importPrice() {
        $r = array();
        foreach ($this->offers->ПакетПредложений->Предложения->Предложение as $prod) {


            $id_external = $prod->Ид;
            $variant = $this->model->variant->getExternalVariant($id_external);
            $pr = $this->getPrice($prod->Цены);
            $variant['price'] = $pr['0c2a1a63-c29f-11e1-89d9-d64b022e3265'];   // забиваем id цены из 1c          
            $variant['stock'] = $prod->Количество;
            $variant['unit'] = $prod->БазоваяЕдиница;

            if (isset($variant['id']) && $variant['id'] > 0) {
                $this->model->variant->update($this->model->variant->checkData($variant, $variant['id_content']), $variant['id']);
                $r[] = 'Вариант обновлен Цена:' . $variant['price'] . ' Кол-во:' . $variant['stock'] . ' Документ ID:' . $variant['id_content'] . ' Название:' . $variant['name'];
            } else {
                //$this->model->variant->insert($this->model->variant->checkData($variant, $variant['id_content']));
                //$r[] = 'Создан новый вариант Цена:' . $variant['price'] . ' Кол-во:' . $variant['stock'] . ' ID:' . $variant['id_content'] . ' Название:' . $variant['name'] . ' Артикул:' . $variant['article'];
                $r[] = 'Такой вариант не найден в базе. ' . $prod->Ид;
            }
        }
        return $r;
    }

    /**
     * Импорт данных из csv в базу
     * @return string 
     */
    public function importCsv() {
        $r = array();
        if (move_uploaded_file($_FILES['filename']['tmp_name'], dirname(__FILE__) . '/../1c/catalog.csv')) {
            $r[] = "Фаил " . basename($_FILES['filename']['name']) . " успешно загружен ";
        } else {
            $r[] = "Не удалось загрузить фаил";
        }
        $f = fopen(dirname(__FILE__) . '/../1c/catalog.csv', "r");

        while ($data = fgetcsv($f, 65000, ';')) {
            $id_variant = $data[2];
            if (is_numeric($id_variant) && $id_variant > 0) {
                $variant['price'] = $data[4];
                $this->model->variant->update($variant, $id_variant);
                $r[] = 'Обновлена цена для:' . $data[3];
            }
        }
        return $r;
    }

    /**
     * Формирует csv фаил содержащий весь каталог. Фаил используется для обновления цен. 
     */
    public function exportCsv() {
        $sql = 'select content.id, content.pagetitle, content.parent,  variant.id_external, variant.price,variant.price2,variant.price3,variant.price4, variant.id as id_variant
                  from ' . $this->modx->getFullTableName(MShopModel::CONTENT) . ' as content                                 
                  left outer join ' . $this->modx->getFullTableName(MShopModel::VARIANT) . ' as variant on (content.id = variant.id_content)                       
                  where 1=1 ';
        // echo $sql;
        $result = $this->modx->db->query($sql);

        while ($row = $this->modx->db->getRow($result)) {
            $output[$row['parent']][$row['id']] = $row;
        }
        $str = '';
        foreach ($output as $id_parent => $parent) {
            $str.= iconv("utf-8", "windows-1251", str_replace('"', '', $output[0][$id_parent]['pagetitle'])) . ";\n\r";
            foreach ($parent as $doc) {
                if ($doc['parent'] > 0) {
                    $str.= '"' . $doc['id_external'] . '";"' . $doc['id'] . '";"' . $doc['id_variant'] . '";"' . iconv("utf-8", "windows-1251", str_replace('"', '', $doc['pagetitle'])) . '";"' . $doc['price'] . '";"' . $doc['price2'] . '";"' . $doc['price3'] . '";"' . $doc['price4'] . '";' . "\n";
                }
            }
        }

        $f = fopen(dirname(__FILE__) . '/../1c/catalog.csv', 'w+');
        fwrite($f, $str);
        fclose($f);
        header("Content-type: application/CSV; charset=windows-1251;");
        header('Content-Disposition: attachment; filename="catalog.csv"');
        print file_get_contents(dirname(__FILE__) . '/../1c/catalog.csv');
        exit();
    }

    /**
     * Возвращает  масив цен товара из 1с.
     * @param type $prices
     * @return array 
     */
    public function getPrice($prices) {
        $res = array();
        foreach ($prices->Цена as $p) {
            $price = str_replace(" руб. за шт", "", $p->Представление);
            $price = rawurlencode($price);
            $price = str_replace("%C2%A0", "", $price);
            $price = str_replace("%2C", ".", $price);
            $id = (string) $p->ИдТипаЦены;
            $res[$id] = $price;
        }

        return $res;
    }

    /**
     * Вставляет зависимость контента от внешней БД. 1с
     * @param type $id_content
     * @param type $id_external
     * @return type 
     */
    public function insertExternalId($id_content, $id_external) {
        $this->modx->db->insert(array('id_content' => $id_content, 'id_external' => $id_external), $this->modx->getFullTableName(MShopModel::EXTERNAL));
        return $this->modx->db->getInsertId();
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
     * Функция для быстрого очистки таблиц mshop. 
     * Приминяется при настройки экспорта\импорта
     * @return string 
     */
    public function emptyCatalog() {
        $r = array();
        $sql = 'TRUNCATE TABLE ' . $this->modx->getFullTableName(MShopModel::VARIANT) . '';
        $this->modx->db->query($sql);
        $r[] = 'Таблица вариантов очищена';

        $sql = 'TRUNCATE TABLE ' . $this->modx->getFullTableName(MShopModel::CONTENT) . '';
        $this->modx->db->query($sql);
        $r[] = 'Таблица контента очищена';

        $sql = 'TRUNCATE TABLE ' . $this->modx->getFullTableName(MShopModel::EXTERNAL) . '';
        $this->modx->db->query($sql);
        $r[] = 'Таблица внешних ключей очищена';
        return $r;
    }

    /**
     * Функция формирования файла с заказами. 
     */
    public function exportOrder() {

        $no_spaces = '<?xml version="1.0" encoding="utf-8"?>
		<КоммерческаяИнформация ВерсияСхемы="2.04" ДатаФормирования="' . date('Y-m-d') . '"></КоммерческаяИнформация>';
        $xml = new SimpleXMLElement($no_spaces);
        $orders = $this->model->order->getOrders();

        foreach ($orders as $order) {

            $doc = $xml->addChild("Документ");
            $doc->addChild("Ид", $order['id']);
            $doc->addChild("Номер", $order['id']);
            $doc->addChild("Дата", date('Y-m-d', strtotime($order['create_date'])));
            $doc->addChild("ХозОперация", "Заказ товара");
            $doc->addChild("Роль", "Продавец");
            $doc->addChild("Курс", "1");
            $doc->addChild("Сумма", $order->price);
            $doc->addChild("Время", date('H:i:s', strtotime($order['create_date'])));
            $doc->addChild("Комментарий", $order['id']);

            // Контрагенты
            $k1 = $doc->addChild('Контрагенты');
            $k1_1 = $k1->addChild('Контрагент');
            $k1_2 = $k1_1->addChild("Ид", $order['name']);
            $k1_2 = $k1_1->addChild("Наименование", $order['name']);
            $k1_2 = $k1_1->addChild("Роль", "Покупатель");
            $k1_2 = $k1_1->addChild("ПолноеНаименование", $order['name']);

            $t1 = $doc->addChild('Товары');
            foreach ($order['products_details'] as $product) {

                $t1_1 = $t1->addChild('Товар');
                $t1_2 = $t1_1->addChild("Ид", $product['id_external']);
//                $t1_2 = $t1_1->addChild("Артикул", $product['article']);
                $name = $product['pagetitle'];
                $t1_2 = $t1_1->addChild("Наименование", $name);
                $t1_2 = $t1_1->addChild("ЦенаЗаЕдиницу", $product['price']);
                $t1_2 = $t1_1->addChild("Количество", $product['count']);
                $t1_2 = $t1_1->addChild("Сумма", $product['price'] * $product['count']);
                $t1_2 = $t1_1->addChild("Единица", 'шт');
                $t1_2 = $t1_1->addChild("Коэффициент", 1);

                $t1_2 = $t1_1->addChild("ЗначенияРеквизитов");
                $t1_3 = $t1_2->addChild("ЗначениеРеквизита");
                $t1_4 = $t1_3->addChild("Наименование", "ВидНоменклатуры");
                $t1_4 = $t1_3->addChild("Значение", "Товар (пр. ТМЦ)");

                $t1_2 = $t1_1->addChild("ЗначенияРеквизитов");
                $t1_3 = $t1_2->addChild("ЗначениеРеквизита");
                $t1_4 = $t1_3->addChild("Наименование", "ТипНоменклатуры");
                $t1_4 = $t1_3->addChild("Значение", "Товар");
            }

            // Статус			
            //    if ($order->status == 1) {
            $s1_2 = $doc->addChild("ЗначенияРеквизитов");
            $s1_3 = $s1_2->addChild("ЗначениеРеквизита");
            $s1_3->addChild("Наименование", "Статус заказа");
            $s1_3->addChild("Значение", "[N] Принят");
            //  }
            /*
              if ($order->status == 2) {
              $s1_2 = $doc->addChild("ЗначенияРеквизитов");
              $s1_3 = $s1_2->addChild("ЗначениеРеквизита");
              $s1_3->addChild("Наименование", "Статус заказа");
              $s1_3->addChild("Значение", "[F] Доставлен");
              }
              if ($order->status == 3) {
              $s1_2 = $doc->addChild("ЗначенияРеквизитов");
              $s1_3 = $s1_2->addChild("ЗначениеРеквизита");
              $s1_3->addChild("Наименование", "Отменен");
              $s1_3->addChild("Значение", "true");
              } */
        }

        //  header("Content-type: text/xml; charset=utf-8");
        $f = fopen(dirname(__FILE__) . '/../1c/order.xml', 'w+');
        fwrite($f, "\xEF\xBB\xBF" . $xml->asXML());
        fclose($f);
        //print "\xEF\xBB\xBF";
        //print $xml->asXML();
        //$simpla->settings->last_1c_orders_export_date = date("Y-m-d H:i:s");
    }

}

?>
