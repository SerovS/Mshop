<?php

/**
 * фаил для сохранения наработок. Для работы mshop вообще не нужен
 */
class Save {

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
                $str_name = $xml_product->Наименование;

                /*                 * **********Разбор артикла, размера, цвета из сторик*********** */
                $f = array();
                //ищем артикул
                preg_match('/, Арт:\d+/', $str_name, $art);

                //ищем размер
                $f['name'] = str_replace($art[0], '', $str_name);
                preg_match('/\d+/', $art[0], $a);

                preg_match('/ р.\d+\-\d+\/\d+\-\d+/', $f['name'], $sizes['t1']);
                preg_match('/ р.\d+\-\d+\/\d+/', $f['name'], $sizes['t3']);
                preg_match('/ р.\d+\/\d+\-\d+/', $f['name'], $sizes['t2']);
                preg_match('/ р.\d+\/\d+/', $f['name'], $sizes['t4']);
                preg_match('/ р.\d+/', $f['name'], $sizes['t5']);

                $flag = true;
                foreach ($sizes as $s) {
                    if (!empty($s[0]) && $flag) {
                        $f['size'] = $s[0];
                        $flag = false;
                        $f['name'] = str_replace($f['size'], '', $f['name']);
                    }
                }
                $f['name'] = trim($f['name']);
                //ищем цвет                
                preg_match('/ц\.([йцукенгшщзхъфывапролджэячсмитьбю]+)/', $f['name'], $color['t1']);
                preg_match('/цв\.([йцукенгшщзхъфывапролджэячсмитьбю]+)/', $f['name'], $color['t2']);
                preg_match('/ц\. ([йцукенгшщзхъфывапролджэячсмитьбю]+)/', $f['name'], $color['t3']);
                preg_match('/цв\. ([йцукенгшщзхъфывапролджэячсмитьбю]+)/', $f['name'], $color['t4']);
                //print_r($color);
                $flag = true;
                foreach ($color as $s) {
                    if (!empty($s[0]) && $flag) {
                        $f['color'] = $s[1];
                        $flag = false;
                        $f['name'] = str_replace($s[0], '', $f['name']);
                    }
                }
                $f['name'] = trim($f['name']);
                //ищем ткань               
                preg_match('/тк\.([йцукенгшщзхъфывапролджэячсмитьбю]+ [йцукенгшщзхъфывапролджэячсмитьбю]+)/', $f['name'], $tk['t0']);
                preg_match('/тк\.([йцукенгшщзхъфывапролджэячсмитьбю]+)/', $f['name'], $tk['t1']);
                preg_match('/тк ([йцукенгшщзхъфывапролджэячсмитьбю]+)/', $f['name'], $tk['t2']);
                preg_match('/тк\. ([йцукенгшщзхъфывапролджэячсмитьбю]+)/', $f['name'], $tk['t3']);

                //print_r($color);
                $flag = true;
                foreach ($tk as $s) {
                    if (!empty($s[0]) && $flag) {
                        $f['tk'] = $s[1];
                        $flag = false;
                        $f['name'] = str_replace($s[0], '', $f['name']);
                    }
                }
                $f['name'] = trim($f['name']);

                //print_r($f);
                //echo $pagetitle . '<br>';
                // Ищем вариант по Ид
                // $id_content = $this->model->document->getExternalDocumentId($xml_product->Ид);
                $variant = $this->model->variant->getExternalVariant($xml_product->Ид);

                $variant = array_merge($variant, array(
                    'article' => intval($xml_product->Артикул),
                    'name' => trim($f['size']) . ' ' . trim($f['color']) . ' ' . trim($f['tk']),
                    'id_external' => intval($xml_product->Ид)
                        ));

                $content = array(
                    'published' => 1,
                    'pagetitle' => $f['name'],
                    'parent' => $this->model->document->getExternalDocumentId($xml_product->Группы->Ид),
                    'template' => $this->model->product_template
                );
                //групируем по имени товары
                $add[$f['name']]['tovar'] = $content;
                $add[$f['name']]['variants'][] = $variant;



                // Проверяем на наличие		
                /*    if (!empty($variant) && is_numeric($variant['id_content'])) {
                  $id_content = $variant['id_content'];
                  $this->model->document->updateDocument($content, $id_content);
                  $res[] = 'Обновлен документ ID:' . $id_content . ' ' . $f['name'] . ' Ид:' . $xml_product->Ид;
                  $this->model->variant->update($this->model->variant->checkData($variant, $id_content), $id_variant);
                  $res[] = 'Обновление варианта  ID:' . $variant['name'] . '(' . $variant['id'] . ') ' . $xml_product->Наименование . ' Ид:' . $xml_product->Ид;
                  } else {
                  if ($id_content > 0) {
                  $this->model->document->updateDocument($content, $id_content);
                  $res[] = 'Обновлен документ ID:' . $id_content . ' ' . $f['name'] . ' Ид:' . $xml_product->Ид;
                  } else {

                  $id_content = $this->model->document->insertDocument($content);
                  $res[] = 'Создан новый документ ID:' . $id_content . ' ' . $f['name'] . ' Ид:' . $xml_product->Ид;
                  }
                  $this->model->variant->insert($this->model->variant->checkData($variant, $id_content));
                  $res[] = 'Создан новый вариант ' . $variant['name'] . ' для документа ID:' . $id_content . ' Ид:' . $xml_product->Ид;
                 */
                // Добавляем изображение товара
                /* if(isset($xml_product->Картинка))
                  {
                  $image = basename($xml_product->Картинка);
                  if(!empty($image) && is_file($dir.$image) && is_writable($simpla->config->original_images_dir))
                  {
                  rename($dir.$image, $simpla->config->original_images_dir.$image);
                  $simpla->products->add_image($product_id, $image);
                  }
                  }
                  } */

                //реализовать потом
                // Подгатавливаем вариант
                /* $variant_id = null;
                  $variant = null;
                  $values = array();
                  if(isset($xml_product->ХарактеристикиТовара->ХарактеристикаТовара))
                  foreach($xml_product->ХарактеристикиТовара->ХарактеристикаТовара as $xml_property)
                  $values[] = $xml_property->Значение;
                  if(!empty($values))
                  $variant->name = implode(', ', $values);
                  $variant->sku = $xml_product->Артикул;
                  $variant->external_id = $variant_1c_id; */

                // Свойства товара
                /* if(isset($xml_product->ЗначенияСвойств->ЗначенияСвойства))
                  foreach ($xml_product->ЗначенияСвойств->ЗначенияСвойства as $xml_option)
                  {
                  $feature_id = $_SESSION['features_mapping'][strval($xml_option->Ид)];
                  if(isset($category_id) && !empty($feature_id))
                  {
                  $simpla->features->add_feature_category($feature_id, $category_id);
                  $values = array();
                  foreach($xml_option->Значение as $xml_value)
                  $values[] = strval($xml_value);
                  $simpla->features->update_option($product_id, $feature_id, implode(' ,', $values));
                  }
                  } */

                // Если нужно - удаляем вариант или весь товар
                /* if ($xml_product->Статус == 'Удален') {
                  $simpla->variants->delete_variant($variant_id);
                  $simpla->db->query('SELECT count(id) as variants_num FROM __variants WHERE product_id=?', $product_id);
                  if ($simpla->db->result('variants_num') == 0)
                  $simpla->products->delete_product($product_id);
                  } */
            }
            //  print_r($add);
            $id_content = 0;
            foreach ($add as $product) {
                foreach ($product['variants'] as $variant) {
                    if (!empty($variant) && is_numeric($variant['id_content'])) {
                        $id_content = $variant['id_content'];
                        //$this->model->document->updateDocument($product['tovar'], $id_content);
                        //$res[] = 'Обновлен документ ID:' . $id_content . ' ' . $f['name'] . ' Ид:' . $xml_product->Ид;
                        $this->model->variant->update($this->model->variant->checkData($variant, $id_content), $id_variant);
                        $res[] = 'Обновление варианта  ID:' . $variant['name'] . '(' . $variant['id'] . ') ' . $product['tovar']['pagetitle'] . ' Ид:' . $variant['id_external'];
                    } else {
                        if ($id_content > 0) {
                            // $this->model->document->updateDocument($product['tovar'], $id_content);
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

    public function importCsv() {
        $f = fopen(dirname(__FILE__) . '/../1c/rec.csv', "r");
        $id_category = 0;
        $r = array();
        while ($data = fgetcsv($f, 65000, ';')) {
            //   print_r($data);
            /* if (empty($data[2])) {

              $fields['pagetitle'] = $data[0];
              $fields['template'] = $this->model->category_template;
              $fields['published'] = 1;
              $fields['parent'] = 0;
              $id_category = $this->model->document->insertDocument($fields);
              $r[] = 'Категория  добавлена - ' . $data[0];
              } else {
              preg_match('/, Арт:\d+/', $data[0], $art);


              $name = str_replace($art[0], '', $data[0]);
              preg_match('/\d+/', $art[0], $a);

              preg_match('/ р.\d+\-\d+\/\d+\-\d+/', $name, $sizes['t1']);
              preg_match('/ р.\d+\-\d+\/\d+/', $name, $sizes['t3']);
              preg_match('/ р.\d+\/\d+\-\d+/', $name, $sizes['t2']);
              preg_match('/ р.\d+/', $name, $sizes['t4']);
              $flag = true;
              foreach ($sizes as $s) {
              if (!empty($s[0]) && $flag) {
              $size = $s[0];
              $flag = false;
              $name = str_replace($s[0], '', $name);
              }
              }


              $fields['pagetitle'] = $name;
              $fields['template'] = $this->model->product_template;
              $fields['published'] = 1;
              $fields['parent'] = $id_category;
              $tov[$name]['fields'] = $fields;
              //$id_content = $this->model->document->insertDocument($fields);
              $r[] = 'Товар  добавлен:' . $name;
              $variant['name'] = $size;
              $variant['article'] = $a[0];
              $variant['price'] = intval(str_replace(" ", '', str_replace(' ', '', $data[3])));
              $variant['unit'] = $data[2];
              $tov[$name]['variants'][] = $variant;
              //$this->model->variant->insert($this->model->variant->checkData($variant, $id_content));
              } */
            $fields['pagetitle'] = $data[1];
            $fields['longtitle'] = $data[2];
            $fields['introtext'] = $data[3];
            $fields['ta'] = $data[4];
            $fields['parent'] = $data[5];
            $fields['template'] = $this->model->product_template;
            $fields['published'] = 1;
            $id_content = $this->model->document->insertDocument($fields);
            $tmplvars['iamge'][0] = 1;
            $tmplvars['iamge'][1] = $data[6];
            $this->model->document->saveTVs($tmplvars, $id_content);
            $r[] = 'Товар  добавлен:' . $data[1];
            $variant['name'] = $data[1];
            $variant['price'] = $data[7];
            $this->model->variant->insert($this->model->variant->checkData($variant, $id_content));
        }

        /* foreach ($tov as $t) {
          $id_content = $this->model->document->insertDocument($t['fields']);
          if (is_array($t['variants'])) {
          foreach ($t['variants'] as $v) {
          $this->model->variant->insert($this->model->variant->checkData($v, $id_content));
          }
          }
          } */

        return $r;
    }
    
                // Доставка
            /*
              if ($order->delivery_price > 0 && !$order->separate_delivery) {
              $t1 = $t1->addChild('Товар');
              $t1->addChild("Ид", 'ORDER_DELIVERY');
              $t1->addChild("Наименование", 'Доставка');
              $t1->addChild("ЦенаЗаЕдиницу", $order->delivery_price);
              $t1->addChild("Количество", 1);
              $t1->addChild("Сумма", $order->delivery_price);
              $t1_2 = $t1->addChild("ЗначенияРеквизитов");
              $t1_3 = $t1_2->addChild("ЗначениеРеквизита");
              $t1_4 = $t1_3->addChild("Наименование", "ВидНоменклатуры");
              $t1_4 = $t1_3->addChild("Значение", "Услуга");

              $t1_2 = $t1->addChild("ЗначенияРеквизитов");
              $t1_3 = $t1_2->addChild("ЗначениеРеквизита");
              $t1_4 = $t1_3->addChild("Наименование", "ТипНоменклатуры");
              $t1_4 = $t1_3->addChild("Значение", "Услуга");
              } */
}

?>
