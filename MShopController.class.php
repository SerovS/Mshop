<?php

/**
 * Class MShopController
 * Изначальная инициализация модулей. Переключение страниц в модуле (back).
 * @author SerovAlexander <serov.sh@gmail.com>
 */
require_once(dirname(__FILE__) . '/models/MShopModel.class.php');
require_once(dirname(__FILE__) . '/views/MShopCatalog.view.php');
require_once(dirname(__FILE__) . '/views/MShopBrand.view.php');
require_once(dirname(__FILE__) . '/views/MShopOrder.view.php');
require_once(dirname(__FILE__) . '/views/MShopProperties.view.php');
require_once(dirname(__FILE__) . '/views/MShopConfig.view.php');
require_once(dirname(__FILE__) . '/views/MShopImport.view.php');

class MShopController {

    public $moduleTitle = 'MODx магазин';
    public $tabs = array(
        'MShopCatalog' => 'Каталог',
        'MShopBrandView' => 'Бренды',
        'MShopOrderView' => 'Заказы',
        'MShopProperties' => 'Свойства',
        'MShopConfig' => 'Настройки',
        'MShopImport' => 'Импорт',
    );
    public $runs = '';
    private static $msgCount = 0;
    public $model;

    public function __construct($path = false, $modx) {
        $this->model = new MShopModel($modx);
    }

    public function run() {

        $params = $this->model->modx->invokeEvent("OnMShopControllerRun");
        $param = unserialize($params[0]);
        if (is_array($param) && !empty($param))
            $this->setParams($param);

        $content = '';
        $content .= '<div class="dynamic-tab-pane-control">';
        $content .= $this->buildMenu($_GET['view']);
        $content .= '<div class="tab-page">';
        $content .= $this->runs;

        if ($_GET['view'] == 'MShopCatalog') {
            $model = new MShopCatalog($this->model);
            $content .= $model->run();
        } elseif ($_GET['view'] == 'MShopConfig') {
            $model = new MShopConfig($this->model);
            $content .= $model->run();
        } elseif ($_GET['view'] == 'MShopProperties') {
            $model = new MShopProperties($this->model);
            $content .= $model->run();
        } elseif ($_GET['view'] == 'MShopBrandView') {
            $model = new MShopBrandView($this->model);
            $content .= $model->run();
        } elseif ($_GET['view'] == 'MShopOrderView') {
            $model = new MShopOrderView($this->model);
            $content .= $model->run();
        } elseif ($_GET['view'] == 'MShopImport') {
            $model = new MShopImport($this->model);
            $content .= $model->run();
        } elseif ($_GET['view'] == 'MShopInstall') {
            $content .= '<div class="sectionHeader">Установка модуля интернет магазина</div>
                 <div class="sectionBody">';
            $content .= $this->model->runInstall();
            $content .='</div>';
        } elseif ($_GET['view'] == 'MShopHelp') {
            $content .= '<div class="sectionHeader">Помощь</div>
                 <div class="sectionBody">';
            $content .= $this->viewHelp();
            $content .='</div>';
        } elseif (empty($this->runs)) {
            $content .= $this->viewSplash();
        }
        $content .= '<a href="' . MShopController::getURL(array('view' => 'MShopHelp')) . '">помощь</a></div></div>';

        $tpl = $this->getMainTemplate();
        $tpl = str_replace('[*content*]', $content, $tpl);
        echo $tpl;
    }

    private function getMainTemplate() {
        return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
                "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                 <html xmlns="http://www.w3.org/1999/xhtml" ' .
                ($this->model->modx->config['manager_direction'] == 'rtl' ? 'dir="rtl"' : '') . ' lang="' .
                $this->model->modx->config['manager_lang_attribute'] . '" xml:lang="' . $this->model->modx->config['manager_lang_attribute'] . '">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title>' . $this->moduleTitle . '</title>			
			<link rel="stylesheet" type="text/css" href="media/style/MODxCarbon/style.css" />
			<link rel="stylesheet" type="text/css" href="../assets/modules/shop/css/styles.css" />
                                           <link rel="stylesheet" type="text/css" href="../assets/modules/shop/js/colorbox/colorbox.css" />

                        <script src="../assets/modules/shop/js/jquery-1.7.min.js" type="text/javascript"></script>
                        <script src="../assets/modules/shop/js/jquery.colorbox.js" type="text/javascript"></script>
		<script type="text/javascript">                             
		var $j = jQuery.noConflict();
		</script>
                       <script src="../assets/modules/shop/js/main.js" type="text/javascript"></script>

		</head>
		<body><div class="container">
                                        [*content*]
                                        </div>
                              </body>
                              </html>            
        ';
    }

    public function commonPage() {
        $res = '';
        $last_docs = $this->model->document->getDocuments(false, 'no', false, 'content.editedon DESC', 10, 0, false, true);
        $res .= '<div class="lc editblock">
                  <h3>Последние измененные страницы</h3>';
        if (is_array($last_docs) && !empty($last_docs)) {
            $res.='<table width="100%">';
            foreach ($last_docs as $doc) {
                if ($this->model->document->isBrand($doc)) {
                    $last_view = 'MShopBrandView';
                    $is = 'Бренд';
                    $template = $this->model->brand_template;
                } elseif ($this->model->document->isCategory($doc)) {
                    $last_view = 'MShopCatalog';
                    $is = 'Категория';
                    $template = $this->model->category_template;
                } else {
                    $last_view = 'MShopCatalog';
                    $is = 'Товар';
                    $template = $this->model->product_template;
                }
                $edit_url = MShopController::getURL(
                                array(
                                    'mshop_id' => $doc['id'],
                                    'mshop' => 1,
                                    'a' => 27,
                                    'id' => $this->model->start_page,
                                    'last_a' => $_GET['a'],
                                    'last_id' => $_GET['id'],
                                    'last_view' => $last_view,
                                    'mshop_template' => $template,
                                )
                );
                $res .= '<tr>
                        <td width="170px;"><a href="' . $edit_url . '"> ' . $doc['pagetitle'] . '</a><br/>
                              <span class="note small">' . date('d.m.Y H:i:s', $doc['editedon']) . '</span> 
                        </td>                        
                        <td class="note">' . $is . '</td>                        
                        <td>' . $doc['price'] . '</td>
                        <td><a href="' . $edit_url . '"> Ред. </a></td>
                       </tr>';
            }
            $res.='</table>';
        }
        $res .= '</div>';

        $res .= '<div class="lc">';
        $new_orders = $this->model->order->getOrders(false, 0, 10);
        if (is_array($new_orders) && !empty($new_orders)) {
            $res .= '<div class="productsdata">
                   <h3>Последнии заказы</h3>';
            $res.='<table width="100%">';
            foreach ($new_orders as $order) {
                $res .= '<tr>
                        <td><a href="' . MShopController::getURL(array('view' => 'MShopOrderView', 'id_order' => $order['id'], 'action' => 'edit',)) . '"> Заказ №' . $order['id'] . '</a> </td>
                        <td>' . $order['status_name'] . '</td>
                        <td><span class="note">' . date('d.m.Y H:i:s', strtotime($order['create_date'])) . ' </span></td>
                        <td>' . $order['price'] . ' ' . $order['currency'] . '</td>
                       </tr>';
            }
            $res .= '</table></div>';
        }
        $res .= '</div>
        <div class="clear"></div>';
        return $res;
    }

    public function installPage() {
        return '<p>Для работы модуля необходимо установить доплнительные таблицы БД. 
                      А так же ряд снипетов и плагинов. Для установки нажмите 
                      <a href="' . MShopController::getURL(array('view' => 'MShopInstall')) . '">установить</a>.</p>
                      <p>Будут установленны. <br />
                      Снипетты:<br />
                      MShopCart - отвечает за вывод корзины <br/>
                      MShopCatalog - вывод каталога<br />
                      Плагины:<br />
                      MShopPlugin1 - работа с каталогом, вывод каталога <br/>
                      MShopPlugin2 - расширение для работы с документами <br />
                      Чанки:<br />
                      cart_tpl - отображение корзины <br/>
                      products_tpl - отображение строки продукции в корзине <br />
                      empty_cart_tpl - отображение пустой корзины <br />
                      </p>';
    }

    private function viewSplash() {
        $res = '<div class="sectionHeader">Добро пожаловать в модуль интернет магазина</div>
                 <div class="sectionBody">';
        if ($this->model->isInstall())
            $res.=$this->commonPage();
        else
            $res.=$this->installPage();

        $res.='</div>';
        return $res;
    }

    private function viewHelp() {
        $res.='<h3>Установка и настройка</h3>';
        $res.='1. Создайте документ который будет родителем для всего каталога. Этот документ будет называтся <b>Точка входа</b><br/>';
        $res.='2. Создайте три шаблона: для товара, для категории (списка товаров), для бренда (если вам это надо)<br/>';
        $res.='3. Зайдите на вкладку настройка и пропишите ID шаблонов<br/>';
        $res.='4. В настройках провертье параметр - URL префикс. Поумолчнию он равен: <i>catalog</i>. Измените его если это необходимо.<br/>';
        $res.='5. В фаил .htaccess вставте строку: RewriteRule ^<i>catalog</i>/([^/]+)?$ index.php?q=<b>Точка входа</b>&mshop_id=$1 [L,QSA] <b>Вставлять перед строкой: RewriteRule ^(.*)$ index.php?q=$1 [L,QSA]</b><br/>';
        $res.='6. Вывод списка товаров: [!MShopCatalog?tpl=`catalog_product_tpl`&parent=`[*id*]`&depth=`2`&limit=`10`&order=`content.menuindex DESC`!]<br/>';
        $res.='7. Для добавления товаров в корзину на странице обязательно должен присутвовать чанк корзины. Вызов чанка: [!MShopCart?cart_tpl=`min_cart_tpl`&products_tpl=`min_products_tpl`!]<br/>';
        $res.='<br><br>Еще больше документации на официальном сайте: <a href="http://mshop.rfweb.su/doc">http://mshop.rfweb.su/doc</a><br><br><br><br>';
        return $res;
    }

    /**     * ***********************************************************************
     * build Tab Menu
     */
    public function buildMenu($active = false) {
        //    if (!$active)
        //      $active = key($this->tabs);

        $buffer = '<div class="tab-row">';
        foreach ($this->tabs as $k => $v) {
            $url = self::getURL(array('a' => $_GET['a'], 'id' => $_GET['id'], 'view' => $k), false);
            if ($active == $k) {
                $buffer .= '<a href="' . $url . '" class="tab selected"><span>' . $v . '</span></a>';
            } else {
                $buffer .= '<a href="' . $url . '" class="tab"><span>' . $v . '</span></a>';
            }
        }
        $buffer .= '</div>';
        return $buffer;
    }

    /**     * ***********************************************************************
     * Helper method that adds a query string to the current url
     * @param $params associative array containing key -> value pairs
     * @param $useGet include variables from $_GET into the array
     * @param $remove variables to remove from query string
     * @return the built url
     */
    public static function getURL(array $params = array(), $useGet = true, array $remove = array()) {
        $self = $_SERVER['PHP_SELF'];
        if ($useGet)
            $params = array_merge($_GET, $params);

        foreach ($remove as $item) {
            if (isset($params[$item]))
                unset($params[$item]);
        }
        return $self . '?' . http_build_query($params, '', '&amp;');
    }

    /**     * ***********************************************************************
     * Helper method to create a removable message element
     * @param $title message title
     * @param $msg message text
     * @param $type the message type. valid values are = info, warning, error
     * @return the built message html code
     */
    public static function message($title, $msg, $type = 'info', $noClose = false) {
        self::$msgCount++;

        if ($noClose) {
            return '<div id="EP_message_' . self::$msgCount . '" class="message ' . $type . '">' .
                    '<div class="msg"><h2>' . $title . '</h2><p>' . $msg . '</p></div>' .
                    '</div><br clear="all"/>';
        } else {
            return '<div id="EP_message_' . self::$msgCount . '" class="message ' . $type . '">' .
                    '<div class="msg"><h2>' . $title . '</h2><p>' . $msg . '</p></div>' .
                    '<a href="#" onclick="$(\'EP_message_' . self::$msgCount . '\').remove(); return false;"' .
                    ' class="messageclose"><span>X</span></a></div><br clear="all"/>';
        }
    }

    public function setParams($param) {
        foreach ($param as $name => $value) {
            $this->$name = $value;
        }
    }

}

?>