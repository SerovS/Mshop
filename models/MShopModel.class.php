<?php

/**
 * 
 * Главная модель модуля. 
 * @author SerovAlexander <serov.sh@gmail.com>
 */
Class MShopModel {

    const CONTENT = 'mshop_content';
    const VARIANT = 'mshop_variant';
    const BRAND = 'mshop_brand';
    const PROPERTY = 'mshop_properties';
    const PROPERTIES2CAT = 'mshop_properties2cat';
    const PROPERTIES = 'mshop_properties_values';
    const TV = 'mshop_tmplvar_contentvalues';
    const ORDER = 'mshop_orders';
    const EXTERNAL = 'mshop_external_ids';

    public $modx;
    public $tv;
    private $config;
    public $limit = 30;
    public $current_page;

    /**
     * Определяем класс $modx как атрибут.
     * Загружаем конфиг. Все значения конфига возможны как атрибуты класса ModuleModel
     * например: $this->value1 
     */
    public function __construct($modx) {
        require_once(dirname(__FILE__) . '/MShopDocument.class.php');
        require_once(dirname(__FILE__) . '/MShopVariant.class.php');
        require_once(dirname(__FILE__) . '/MShopBrand.class.php');
        require_once(dirname(__FILE__) . '/MShopProperty.class.php');
        require_once(dirname(__FILE__) . '/MShopOrder.class.php');

        $this->modx = $modx;
        
        foreach ($this->getConfig() as $name => $value)
            $this->$name = $value[0];


        $this->document = new MShopDocument($modx, $this);
        $this->variant = new MShopVariant($modx, $this);
        $this->brand = new MShopBrand($modx, $this);
        $this->property = new MShopProperty($modx, $this);
        $this->order = new MShopOrder($modx, $this);

        if (isset($_GET[$this->get_catalog]) && is_numeric($_GET[$this->get_catalog]))
            $this->current_page = $_GET[$this->get_catalog];

        $params = $modx->invokeEvent("OnMShopModelInit");
        $param = unserialize($params[0]);
        if (is_array($param) && !empty($param))
            $this->setParams($param);
    }

    /**
     * Функция примера. 
     */
    public function getModuleData() {
        throw new Exception('Возникла ошибка', 0);
        return 'Значение для модуля';
    }

    /**
     * Устанавливаем значение tv параметра для документа
     * @param type $id_tv ID tv параметра
     * @param type $value устанавливаемое значение
     * @param type $id_doc ID документа для которого необходимо установить значение
     */
    public function saveTv($id_tv, $value, $id_doc) {
        $tvtable = $this->modx->db->config['table_prefix'] . self::TV;
        $sql = 'select * from ' . $tvtable . ' as tv  where tv.tmplvarid=\'' . $id_tv . '\' and tv.contentid=\'' . $id_doc . '\'';
        $result = $this->modx->db->query($sql);
        $tv = $this->modx->db->getRow($result);
        if (is_numeric($tv['id'])) {
            $sql = 'update ' . $tvtable . ' as tv set value=\'' . $value . '\'  where tv.id=\'' . $tv['id'] . '\'';
            $result = $this->modx->db->query($sql);
        } else {
            $sql = 'insert into ' . $tvtable . '  (`tmplvarid` ,
                                                                        `contentid` ,
                                                                        `value`)
                                                                        values (
                                                                        \'' . $id_tv . '\',
                                                                        \'' . $id_doc . '\',
                                                                        \'' . $value . '\'
                                                                        )';
            $result = $this->modx->db->query($sql);
        }
    }

    /**
     * Отдаем массив конфигурации в модуль из файла config.php
     * @return type 
     */
    public function getConfig() {
        if (is_array($this->config))
            return $this->config;
        if (file_exists(dirname(__FILE__) . '/config.php')) {
            $this->config = unserialize(require(dirname(__FILE__) . '/config.php'));
            $groups = MShopVariant::getWebGroups($this->modx);
            foreach ($groups as $id_group => $group) {
                if (!isset($this->config['discount_' . $id_group]))
                    $this->config['discount_' . $id_group] = array(0, 'Скидка для группы пользователей <sctrong>' . $group['name'] . '</strong>:');
            }
        }else
            $this->createConfig();
        return $this->config;
    }

    /**
     * Создаем фаил конфигурации модуля. config.php
     * Тут задаем возможные значения и значения по умолчанию. 
     */
    protected function createConfig() {
        @fopen(dirname(__FILE__) . '/config.php', 'a') or die("Невозможно создать фаил конфигурации, поставте на папку assets/modules/shop/models права 777");
        $config = array(
            'start_page' => array(5, 'Точка входа магазина'),
            'product_template' => array(6, 'ID шаблона товара'),
            'category_template' => array(5, 'ID шаблона категории'),
            'brand_template' => array(8, 'ID шаблона бренда'),
            'user_email_tpl' => array('mshopUserReport', 'Чанк письма для заказчика'),
            'run_catalog' => array(1, 'Использовать каталог в отдельной БД'),
            'get_catalog' => array('mshop_id', 'GET параметр отвечающий за вывод документов из каталога'),
            'url_catalog' => array('catalog', 'URL префикс кторый использыется для формирования url к докуметам в каталоге'),
        );
        $this->setConfig($config);
        $this->saveConfig();
    }

    /**
     * Устанавливаем значение конфигурации для модуля.
     * @param <array> $arr массив значений
     */
    public function setConfig($arr) {        
        foreach ($arr as $name => $value) {
            if (is_array($value))
                $this->config[$name] = $value;
            elseif(stripos($value, ','))
                $this->config[$name][0] = explode(',',$value);
            else
                $this->config[$name][0] = $value;
        }
    }

    /**
     * Сохраняем фаил конфигурации. 
     */
    public function saveConfig() {
        $f = fopen(dirname(__FILE__) . '/config.php', 'w') or die("Невозможно открыть фаил конфигурации");
        $in = '<?php' . "\n";
        $in .= 'return \'';
        $in .= serialize($this->config);
        $in .= '\';' . "\n";
        $in .= '?>';
        fwrite($f, $in);
        fclose($f);
    }

    /**
     * Добавляем товар в корзину
     * @param type $id_variant
     * @param type $count
     * @throws Exception 
     */
    public function addCart($id_variant, $count = 1) {
        if (is_numeric($count) && $count > 0) {
            $variants = $this->getCartVariants();
            $variants[$id_variant] = $count;
            $_SESSION['MShop_variants'] = serialize($variants);
        } elseif ($count == 0) {
            $this->removeCart($id_variant);
        }else
            throw new Exception('Количество товара не является целым числом', 0); //lang
    }

    /**
     * Удаляем товар из корзины
     * @param type $id_variant 
     */
    public function removeCart($id_variant) {
        $variants = $this->getCartVariants();
        unset($variants[$id_variant]);
        $_SESSION['MShop_variants'] = serialize($variants);
    }

    public function emptyCart() {
        $_SESSION['MShop_variants'] = array();
    }

    /**
     * Вытаскиваем из сессии id товаров
     * @return array
     */
    public function getCartVariants() {
        if (is_string($_SESSION['MShop_variants']))
            return unserialize($_SESSION['MShop_variants']);
        else
            return $_SESSION['MShop_variants'];
    }

    /**
     * Получаем идентификаторы товаров которые лежат в корзине
     * @return array
     */
    public function getCartInfo() {
        $res = array();
        $total = 0;
        $vars = $this->getCartVariants();
        if (is_array($vars)) {
            foreach ($vars as $id => $count) {
                $ids[$id] = $id;
            }
            $docs = $this->variant->getDocuments($ids);
            foreach ($vars as $id => $count) {
                if ($this->document->isProduct($docs[$id])) {
                    $res['products'][$id] = $docs[$id];
                    $res['products'][$id]['count'] = $count;
                    $total += $docs[$id]['price'] * $count;
                }
            }
            $res['total'] = $total;
        }
        return $res;
    }

    /**
     *  Функция формирования html кода корзины
     * @param type $result
     * @param type $cart_tpl
     * @param type $products_tpl
     * @param type $empty_cart_tpl
     * @return type
     * @throws Exception 
     */
    public function getCartHtml($result, $cart_tpl = false, $products_tpl = false, $empty_cart_tpl = false) {
        if ($cart_tpl == false || $cart_tpl == 'default')
            throw new Exception('Чанк корзины не определен', 0); //lang
        if ($products_tpl == false || $products_tpl == 'default')
            throw new Exception('Чанк продукта в корзине не определен', 0); //lang
        if ($empty_cart_tpl == false || $empty_cart_tpl == 'default')
            throw new Exception('Чанк пустой корзины не определен', 0); //lang
        $result['products_html'] = '';
        if (is_array($result['products']) && !empty($result['products'])) {
            foreach ($result['products'] as $product) {
                $result['products_html'] .= $this->modx->parseChunk($products_tpl, $product, '[+', '+]');
            }
        }else
            $cart_tpl = $empty_cart_tpl; //подмена шаблона для пустой корзины

        return $this->modx->parseChunk($cart_tpl, $result, '[+', '+]');
    }

    public function validString($str) {
        $valids = 'abcdefghijklmnopqrstuvwxyz1234567890_';
        return strlen($str) == strspn($str, $valids);
    }

    /**
     * Функция отправки письма.
     * @param string $subject
     * @param string $email
     * @param string $body
     * @throws Exception 
     */
    public function sendMail($subject, $email, $body) {
        $charset = $this->modx->config['modx_charset'];
        $site_name = $this->modx->config['site_name'];
        $adminEmail = $this->modx->config['emailsender'];
        require_once(MODX_MANAGER_PATH . "includes/controls/class.phpmailer.php");
        $mail = new PHPMailer();
        $mail->IsMail();
        $mail->IsHTML(true);
        $mail->CharSet = $charset;
        $mail->From = $adminEmail;
        $mail->FromName = $site_name;
        $mail->Subject = $subject;
        $mail->Body = $body;
//        echo $body;
        $mail->AddAddress($email);
        if (!$mail->send()) {
            throw new Exception($mail->ErrorInfo, 0); //lang            
        }
    }

    /**
     * Возвращает страницы для модуля (back)
     * @param type $count
     * @param type $limit
     * @param type $current
     * @return string 
     */
    public function getPager($count, $limit, $current = false) {
        if ($count / $limit > floor($count / $limit)) {
            $pages = $count / $limit + 1;
        } else {
            $pages = $count / $limit;
        }
        $res = $class = '';
        for ($i = 1; $i <= $pages; $i++) {
            $start = ($i * $limit) - $limit;
            if ($current == $start)
                $class = 'active';
            $res .= '<a href="' . MShopController::getURL(array('mshop_start' => $start)) . '" class="pager ' . $class . '">' . $i . '</a>';
            $class = '';
        }
        return $res;
    }

    /**
     * Проверка на установленность таблиц. 
     * @return boolean 
     */
    public function isInstall() {
        $sql = 'SELECT count(*) as count FROM information_schema.tables WHERE table_name = \'' . $this->modx->db->config['table_prefix'] . self::CONTENT . '\'';
        $result = $this->modx->db->query($sql);
        $row = $this->modx->db->getRow($result);
        if ($row['count'] == 1)
            return true;
        return false;
    }

    /**
     * Запуск инстолятора. Устанавливает таблицы и необходимые снипеты
     * @return string 
     */
    public function runInstall() {
        if ($this->isInstall())
            return 'Модуль уже установлен!';
        $res = array();
        require_once(dirname(__FILE__) . '/install.php');
        return implode('<br/>', $res);
    }
    
    /**
     * Устанавливает дополнительные атрибуты
     * @param array $param массив атрибутов
     */
    public function setParams($param) {
        foreach ($param as $name => $value) {
            $this->$name = $value;
        }
    }

}

?>