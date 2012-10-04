<?php

if ($_POST['demo'])
    $res[] = "Демо товары установлены!";


$res[] = '<form method="POST">
    
<label><input type="checkbox" name="demo" value="1"> Установить демо товары</label> <br>
<label><input type="checkbox" name="template" value="1"> Установить 3 новых шаблона (товар, категория, бренд)</label> <br>
<input type="submit" value="установить">
</form>';

if ($_POST['install'] == 1) {

    $sql = 'CREATE TABLE IF NOT EXISTS `[+prefix+]mshop_brand` (
  `id_content` int(11) NOT NULL,
  `id_brand` int(11) NOT NULL,
  PRIMARY KEY (`id_content`),
  KEY `id_brand` (`id_brand`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
    $sql = str_replace('[+prefix+]', $this->modx->db->config['table_prefix'], $sql);
    $result = $this->modx->db->query($sql);
    if ($result)
        $res[] = 'Таблица mshop_brand установленна';

    $sql = "CREATE TABLE IF NOT EXISTS `[+prefix+]mshop_content` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `pagetitle` varchar(255) NOT NULL DEFAULT '',
  `longtitle` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) DEFAULT '',
  `published` int(1) NOT NULL DEFAULT '0',
  `pub_date` int(20) NOT NULL DEFAULT '0',
  `parent` int(10) NOT NULL DEFAULT '0',
  `isfolder` int(1) NOT NULL DEFAULT '0',
  `introtext` text COMMENT 'Used to provide quick summary of the document',
  `content` mediumtext,
  `template` int(10) NOT NULL DEFAULT '1',
  `menuindex` int(10) NOT NULL DEFAULT '0',
  `menutitle` varchar(255) NOT NULL DEFAULT '' COMMENT 'Menu title',
  `hidemenu` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Hide document from menu',
  `richtext` tinyint(1) NOT NULL DEFAULT '1',
  `editedon` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `parent` (`parent`),
  KEY `aliasidx` (`alias`),
  FULLTEXT KEY `content_ft_idx` (`pagetitle`,`description`,`content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Contains the site document tree.' AUTO_INCREMENT=1 ;";
    $sql = str_replace('[+prefix+]', $this->modx->db->config['table_prefix'], $sql);
    $result = $this->modx->db->query($sql);
    if ($result)
        $res[] = 'Таблица mshop_content установленна.';


    $sql = 'CREATE TABLE IF NOT EXISTS `[+prefix+]mshop_external_ids` (
  `id_content` int(11) NOT NULL,
  `id_external` varchar(255) NOT NULL,
  PRIMARY KEY (`id_content`,`id_external`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
    $sql = str_replace('[+prefix+]', $this->modx->db->config['table_prefix'], $sql);
    $result = $this->modx->db->query($sql);
    if ($result)
        $res[] = 'Таблица соответствий для 1с установленна (mshop_external_ids)';


    $sql = "CREATE TABLE IF NOT EXISTS `[+prefix+]mshop_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_delivery` int(11) DEFAULT NULL,
  `delivery_price` float(10,2) NOT NULL DEFAULT '0.00',
  `id_payment` int(11) DEFAULT NULL,
  `payment_status` int(11) NOT NULL DEFAULT '0',
  `payment_date` datetime NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `id_user` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `user_details` text NOT NULL,
  `phone` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `comment` varchar(1024) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `tracking_num` varchar(255) DEFAULT NULL,
  `products_details` text NOT NULL,
  `ip` varchar(15) NOT NULL,
  `send_date` datetime DEFAULT NULL,
  `edit_date` datetime DEFAULT NULL,
  `recall_date` datetime DEFAULT NULL,
  `currency` varchar(15) NOT NULL,
  `price` double NOT NULL,
  `access_key` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `login` (`id_user`),
  KEY `date` (`create_date`),
  KEY `status` (`status`),
  KEY `code` (`tracking_num`),
  KEY `payment_status` (`payment_status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
    $sql = str_replace('[+prefix+]', $this->modx->db->config['table_prefix'], $sql);
    $result = $this->modx->db->query($sql);
    if ($result)
        $res[] = 'Таблица для работы с заказами установленна (mshop_order)';


    $sql = "CREATE TABLE IF NOT EXISTS `[+prefix+]mshop_properties` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `in_product` int(1) DEFAULT NULL,
  `in_filter` int(1) DEFAULT NULL,
  `in_compare` int(1) DEFAULT NULL,
  `enabled` int(1) NOT NULL DEFAULT '1',
  `options` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
    $sql = str_replace('[+prefix+]', $this->modx->db->config['table_prefix'], $sql);
    $result = $this->modx->db->query($sql);
    if ($result)
        $res[] = 'Таблица дополнительных свойств установленна (mshop_properties)';

    $sql = "CREATE TABLE IF NOT EXISTS `[+prefix+]mshop_properties2cat` (
  `id_property` int(11) NOT NULL,
  `id_content` int(11) NOT NULL,
  PRIMARY KEY (`id_property`,`id_content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    $sql = str_replace('[+prefix+]', $this->modx->db->config['table_prefix'], $sql);
    $result = $this->modx->db->query($sql);
    if ($result)
        $res[] = 'Таблица соответствия свойств и категорий установленна (mshop_properties2cat)';


    $sql = "CREATE TABLE IF NOT EXISTS `[+prefix+]mshop_properties_values` (
  `id_content` int(11) NOT NULL,
  `id_property` int(11) NOT NULL,
  `value` varchar(512) NOT NULL,
  PRIMARY KEY (`id_content`,`id_property`),
  KEY `value` (`value`(333))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    $sql = str_replace('[+prefix+]', $this->modx->db->config['table_prefix'], $sql);
    $result = $this->modx->db->query($sql);
    if ($result)
        $res[] = 'Таблица содержащая значения свойств установленна (mshop_properties_values)';

    $sql = "CREATE TABLE IF NOT EXISTS `[+prefix+]mshop_tmplvar_contentvalues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tmplvarid` int(10) NOT NULL DEFAULT '0' COMMENT 'Template Variable id',
  `contentid` int(10) NOT NULL DEFAULT '0' COMMENT 'Site Content Id',
  `value` text,
  PRIMARY KEY (`id`),
  KEY `idx_tmplvarid` (`tmplvarid`),
  KEY `idx_id` (`contentid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
    $sql = str_replace('[+prefix+]', $this->modx->db->config['table_prefix'], $sql);
    $result = $this->modx->db->query($sql);
    if ($result)
        $res[] = 'Таблица содержащая TV параметры документов установленна (mshop_tmplvar_contentvalues)';

    $sql = "CREATE TABLE IF NOT EXISTS `[+prefix+]mshop_variant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_content` int(11) NOT NULL,
  `article` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` float(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `unit` varchar(55) NOT NULL,
  `id_external` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
    $sql = str_replace('[+prefix+]', $this->modx->db->config['table_prefix'], $sql);
    $result = $this->modx->db->query($sql);
    if ($result)
        $res[] = 'Таблица вариантов установленна  (mshop_variant)';

    $sql = "INSERT INTO " . $this->modx->getFullTableName('site_snippets') . "
            (`name`, `description`, `editor_type`, `category`, `cache_type`, `snippet`, `locked`, `properties`, `moduleguid`) 
            VALUES ('MShopCart', '', 0, 0, 0, '\r\nrequire MODX_BASE_PATH.\"assets/modules/shop/MShopCart.php\";\r\n', 0, '', ' ');";
    $result = $this->modx->db->query($sql);
    if ($result)
        $res[] = 'Снипет MShopCart установлен';
    $sql = "INSERT INTO " . $this->modx->getFullTableName('site_snippets') . "
            (`name`, `description`, `editor_type`, `category`, `cache_type`, `snippet`, `locked`, `properties`, `moduleguid`) 
            VALUES ('MShopCatalog', '', 0, 0, 0, '\r\nrequire MODX_BASE_PATH.\"assets/modules/shop/MShopCatalog.php\";\r\n', 0, '', ' ');";
    $result = $this->modx->db->query($sql);
    if ($result)
        $res[] = 'Снипет MShopCatalog установлен';
    $sql = "INSERT INTO " . $this->modx->getFullTableName('site_snippets') . "
            (`name`, `description`, `editor_type`, `category`, `cache_type`, `snippet`, `locked`, `properties`, `moduleguid`) 
            VALUES ('MShopMenu', '', 0, 0, 0, '\r\nrequire MODX_BASE_PATH.\"assets/modules/shop/MShopMenu.php\";\r\n', 0, '', ' ');";
    $result = $this->modx->db->query($sql);
    if ($result)
        $res[] = 'Снипет MShopMenu установлен';

    $sql = "INSERT INTO " . $this->modx->getFullTableName('site_snippets') . "
            (`name`, `description`, `editor_type`, `category`, `cache_type`, `snippet`, `locked`, `properties`, `moduleguid`) 
            VALUES ('MShopOrder', '', 0, 0, 0, '\r\nrequire MODX_BASE_PATH.\"assets/modules/shop/MShopOrder.php\";\r\n', 0, '', ' ');";
    $result = $this->modx->db->query($sql);
    if ($result)
        $res[] = 'Снипет MShopOrder установлен';

    $sql = "INSERT INTO " . $this->modx->getFullTableName('site_snippets') . "
            (`name`, `description`, `editor_type`, `category`, `cache_type`, `snippet`, `locked`, `properties`, `moduleguid`) 
            VALUES ('MShopBreadcrumbs', '', 0, 0, 0, '\r\nrequire MODX_BASE_PATH.\"assets/modules/shop/MShopBreadcrumbs.php\";\r\n', 0, '', ' ');";
    $result = $this->modx->db->query($sql);
    if ($result)
        $res[] = 'Снипет MShopBreadcrumbs установлен';

    $sql = "INSERT INTO " . $this->modx->getFullTableName('site_plugins') . " (`name`, `description`, `editor_type`, `category`, `cache_type`, `plugincode`, `locked`, `properties`, `disabled`, `moduleguid`) VALUES
                ('MShop', '', 0, 0, 0, 'require_once MODX_BASE_PATH.\"assets/modules/shop/plugin.php\";', 0, '', 0, ' ');";
    $result = $this->modx->db->query($sql);
    if ($result) {

        $id_plugin = $this->modx->db->getInsertId();
        $sql = "INSERT INTO " . $this->modx->getFullTableName('site_plugin_events') . " (`pluginid`, `evtid`, `priority`) VALUES
                (" . $id_plugin . ", 28, 1),
                (" . $id_plugin . ", 30, 1),
                (" . $id_plugin . ", 91, 1)
                ;";
        $result = $this->modx->db->query($sql);
        if ($result)
            $res[] = 'Плагин1 установлен';
    }


    $sql = "INSERT INTO " . $this->modx->getFullTableName('site_plugins') . " (`name`, `description`, `editor_type`, `category`, `cache_type`, `plugincode`, `locked`, `properties`, `disabled`, `moduleguid`) VALUES
                ('MShop2', '', 0, 0, 0, 'require_once MODX_BASE_PATH.\"assets/modules/shop/plugin2.php\";', 0, '', 0, ' ');";
    $result = $this->modx->db->query($sql);
    if ($result) {

        $id_plugin = $this->modx->db->getInsertId();
        $sql = "INSERT INTO " . $this->modx->getFullTableName('site_plugin_events') . " (`pluginid`, `evtid`, `priority`) VALUES
                (" . $id_plugin . ", 29, 1);";
        $result = $this->modx->db->query($sql);
        if ($result)
            $res[] = 'Плагин2 установлен';
    }


    $sql = "INSERT INTO " . $this->modx->getFullTableName('site_htmlsnippets') . " (`name`, `description`, `editor_type`, `category`, `cache_type`, `snippet`, `locked`) VALUES
( 'cart_tpl', 'расширенный код корзины при оформлении заказа', 0, 0, 0, '" . '<div>\r\n<h1>Корзина</h1>\r\n<form class="mshop_cart" action="/assets/modules/shop/ajax.php" method="POST">\r\n<input type="hidden" name="MShop_action" value="add">\r\n<table>\r\n<tr>\r\n<th>Наиименование</th>\r\n<th>Кол-во</th>\r\n<th>Цена</th>\r\n<th></th>\r\n</tr>\r\n[+products_html+]\r\n<tr>\r\n<td colspan="2">На сумму:</td>\r\n<td colspan="2">[+total+]</td>\r\n</tr>\r\n</table>\r\n  <input type="submit" value="пересчитать">\r\n</form>\r\n\r\n\r\n</div>' . "', 0),
( 'products_tpl', 'код для одной строки в корзине', 0, 0, 0, '" . '<tr>\r\n<td><a href="">[+pagetitle+] ([+name+] [+article+])</a> ([+id_variant+])</td>\r\n<td><input type="text" value="[+count+]" name="MShop_variant[[+id_variant+]]" onChange="addCart(this, [+id_variant+], this.value)"></td>\r\n<td>[+price+]</td>\r\n<td><a href="javascript:;" onClick="deleteCart(this, [+id_variant+]);">Удалить</a></td>\r\n</tr>' . "', 0),
( 'min_cart_tpl', 'код для минимальной корзины', 0, 0, 0, '" . '<p>\r\n<strong class="blue">Корзина</strong>\r\n</p>\r\n[+products_html+]\r\nтовара на сумму\r\n<p>\r\n<strong class="blue">\r\n[+total+] рублей\r\n</strong>\r\n</p>\r\n<form>\r\n<button class="bluebutton rounded" formaction="/11">Оформить заказ</button>\r\n</form>' . "', 0),
( 'min_products_tpl', 'код одной строки товара для минимальной корзины', 0, 0, 0, '" . '<a href="[+url+]">[+pagetitle+]</a> - [+price+] [+count+]шт.\r\n<br/><br/>' . "', 0),
( 'catalog_product_tpl', 'шаблон для списка товаров', 0, 0, 0, '" . '
<div class="product">\r\n
<form class="mshop_product" action="/assets/modules/shop/ajax.php" method="POST">\r\n
      <a href="[+url+]" class="product_name">[+pagetitle+] </a>\r\n
      <div id="livecart_animate[+id_variant+]" style="display:none;" class="helper">\r\n
	  <a href="[+url+]">\r\n
	  <img src="[+tv1+]" alt="[+longtitle+]" />\r\n
	  </a>\r\n
      </div>\r\n
      <div class="product_image">\r\n
	  <a href="[+url+]">\r\n
	  <img src="[+tv1+]" alt="[+longtitle+]" />\r\n
	  </a>\r\n
      </div>\r\n
      <div class="product_desc" >\r\n	       	  
	  <p>[+introtext+]</p>\r\n      
	  <span class="product_price">[+price+]&nbsp;руб</span> <br />\r\n
    <input type="hidden" name="MShop_action" value="add">\r\n
    <input type="hidden" name="MShop_variant" value="[+id_variant+]" class="MShop_variant">\r\n
    <input type="submit" value="купить">	  \r\n
      </div>\r\n
\r\n
</form>\r\n
    </div>    \r\n' . "', 0),
( 'helper', 'код хелпера', 0, 0, 0, '" . '
    <div id="MShopHelper">\r\n
	<a onclick="hideHelper();return false;" id="cancelButton" href="javascript:;" title="Закрыть">X</a>\r\n	

	<label>Кол-во:<input type="text" size="10" class="quantity" name="count" value="1" id="MShop_count"/></label>\r\n
	<a onclick="upHelper();return false;" href="javascript:;" id="up" title="Увеличить кол-во">&uarr;</a>\r\n
	<a onclick="downHelper();return false;" id="down" href="javascript:;" title="Уменьшить кол-во">&darr;</a>		\r\n

	<button class="bluebutton rounded" onclick="sendHelper(this);return false;">Добавить</button>\r\n
</div><!--/mshophelper-->\r\n
' . "', 0),
( 'empty_cart_tpl', 'код пустой корзины', 0, 0, 0, '" . '<div>\r\n<h1>Корзина</h1>\r\n<p>Корзина пуста</p>\r\n\r\n</div>' . "', 0);";

    $result = $this->modx->db->query($sql);
    if ($result)
        $res[] = 'Чанки cart_tpl,products_tpl,empty_cart_tpl, min_products_tpl, min_cart_tpl, catalog_product_tpl установлены';

    $sql = "INSERT INTO " . $this->modx->getFullTableName('system_eventnames') . "  (
`id` ,`name` ,`service` ,`groupname`)
VALUES (NULL , 'OnMShopOrderFrontView', '7', 'MShop'),
 (NULL , 'OnMShopCartFrontInit', '7', 'MShop'),
 (NULL , 'OnMShopModelInit', '7', 'MShop'),
 (NULL , 'OnMShopControllerRun', '7', 'MShop')
;";
    $result = $this->modx->db->query($sql);
    if ($result)
        $res[] = 'Системные события установлены';
}

$res[] = '<h3>Установка и настройка</h3>';
$res[] = '1. Создайте документ который будет родителем для всего каталога. Этот документ будет называтся <b>Точка входа</b>';
$res[] = '2. Создайте три шаблона: для товара, для категории (списка товаров), для бренда (если вам это надо)';
$res[] = '3. Зайдите на вкладку настройка и пропишите ID шаблонов';
$res[] = '4. В настройках провертье параметр - URL префикс. Поумолчнию он равен: <i>catalog</i>. Измените его если это необходимо.';
$res[] = '5. В фаил .htaccess вставте строку: RewriteRule ^<i>catalog</i>/([^/]+)?$ index.php?q=<b>Точка входа</b>&mshop_id=$1 [L,QSA] <b>Вставлять перед строкой: RewriteRule ^(.*)$ index.php?q=$1 [L,QSA]</b>';
$res[] = '6. Вывод списка товаров: [!MShopCatalog?tpl=`catalog_product_tpl`&parent=`[*id*]`&depth=`2`&limit=`10`&order=`content.menuindex DESC`!]';
$res[] = '7. Для добавления товаров в корзину на странице обязательно должен присутвовать чанк корзины. Вызов чанка: [!MShopCart?cart_tpl=`min_cart_tpl`&products_tpl=`min_products_tpl`!]';
$res[] = '<br><br>Еще больше документации на официальном сайте: <a href="http://mshop.rfweb.su/doc">http://mshop.rfweb.su/doc</a><br><br><br><br>';
?>
