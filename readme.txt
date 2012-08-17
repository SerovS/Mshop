Модуль магазина для CMS MODx.
Особенности:
Использование api modx
Обращение к объекту $modx как атрибуту класса MSopModel
Разделение на модель, контроллер и представление
Вывод ошибок и сообщений

Подключение модуля.
Модули -> Управление Моудлями -> Новый Модуль добавить код:

if(!isset($modx)) die();

$basePath = $modx->config['base_path'];
$modulePath = $basePath . 'assets/modules/shop/';

// ------------------------------------------------------------------------------
// Create Controller
// ------------------------------------------------------------------------------
$classfile = $modulePath . 'MShopController.class.php';
if(!file_exists($classfile))
	$modx->messageQuit(sprintf('Файл %s несуществует', $classfile));

require_once($classfile);
$controller = new MShopController($modulePath, $modx);
try {
	$controller->run();
} catch (Exception $ex){
	$modx->messageQuit($ex->getMessage());
}
return;


Удалить фаил конфига config.php, поставить права на папку modules/shop/models 777. 


Документация

Пример вызовов яваскрипта для добавления товара в корзину:
<a href="javascript:;" onClick="addCart(this);">В корзину 17</a>
<a href="javascript:;" onClick="addCart(this, 15);">В корзину15</a>
<a href="javascript:;" onClick="deleteCart(this, 15);">Удалить из корзины</a>
<a href="javascript:;" onClick="emptyCart();">Очистить корзину</a>
<a href="javascript:;" onClick="addCart(this, 9, 8);">В корзину 9 - 8шт</a>
<a href="javascript:;" onClick="refreshCart();">Обновить</a>
<a href="javascript:;" onClick="refreshCart(1);">Обновить HTML</a>

События
OnMShopCartFrontInit
OnMShopOrderFrontView
OnMShopModelInit
OnMShopControllerRun