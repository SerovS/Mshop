<?php

/**
 * Вспомогательный плагин. Обеспечивает оплату товаров через сервис robokassa
 * 
 * @author SerovAlexander <serov.sh@gmail.com>
 */
//Первоночальные установки
$wmid = "1234567890"; //номер вашего кошелька
$mrh_login = "demo";
$mrh_pass1 = "pass1";
$mrh_pass2 = "pass2";
// тип товара
$shp_item = 1;
// предлагаемая валюта платежа
$in_curr = "WMR";
// кодировка
$encoding = "utf-8";

$out_summ = $_REQUEST["OutSum"];
$inv_id = $_REQUEST["InvId"];
$shp_item = $_REQUEST["Shp_item"];
$crc = $_REQUEST["SignatureValue"];
$crc = strtoupper($crc);
$action = isset($action) ? $action : '';

if ($action == 'success') {    
    $my_crc = strtoupper(md5("$out_summ:$inv_id:$mrh_pass1:Shp_item=$shp_item"));
    if ($my_crc == $crc) {
        require_once MODX_BASE_PATH . 'assets/modules/shop/models/MShopModel.class.php';
        $mshop = new MShopModel($modx);
        $order = $mshop->order->getOrder($inv_id);
        header('Location:' . $order['url']);
    }
}

if ($action == 'result') {
    $my_crc = strtoupper(md5("$out_summ:$inv_id:$mrh_pass2:Shp_item=$shp_item"));
    if ($my_crc == $crc) {
        echo "OK" . $inv_id;
        require_once MODX_BASE_PATH . 'assets/modules/shop/models/MShopModel.class.php';
        $mshop = new MShopModel($modx);
        $order = $mshop->order->getOrder($inv_id);
        if ($order['status'] < 15) {
            $mshop->order->payOrder($order);
        }
    }
}

//Начало кода для плагина
$e = &$modx->Event;
$output = '';
switch ($e->name) {

    case 'OnMShopOrderFrontView':
        $order = $modx->event->params;

        if ($order['user_details_payment'] == 'Robokassa' && $order['status'] < 15) {

            $inv_desc = "Оплата заказа №" . $order['id'];
            $out_summ = $order['price'];
            $inv_id = $shp_item = $order['id'];
            // формирование подписи
            $crc = md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1:Shp_item=$shp_item");


            $order['payment_link'] = "<div class='paybox'>
				    <h3>Оплатить покупку используя сервис robocassa</h3>
				    <span class='price big'>К оплате: " . $out_summ . "  рублей.</span> <br />


				    <form action='http://test3.rfweb.su' method=POST>" .
                                                                    "<input type=hidden name=MrchLogin value=$mrh_login>" .
                                                                    "<input type=hidden name=OutSum value=$out_summ>" .
                                                                    "<input type=hidden name=InvId value=$inv_id>" .
                                                                    "<input type=hidden name=Desc value='$inv_desc'>" .
                                                                    "<input type=hidden name=SignatureValue value=$crc>" .
                                                                    "<input type=hidden name=Shp_item value='$shp_item'>" .
                                                                    "<input type=hidden name=IncCurrLabel value=$in_curr>" .
                                                                    "<input type=hidden name=Culture value=$culture>" .
                                                                    "<label>Ваш электронный ящик<sup>1</sup>: <input type=text name=Email value=" . $order['email'] . "></label><br />" .
                                                                    "<input type=submit value='Оплатить' class='button big'><br />
                                                                    <img border='0' src='/img/ya.gif'>
                                                                    <img border='0' src='/img/mts.gif'>
                                                                    <img border='0' src='/img/megafon.gif'>
                                                                    <img border='0' src='/img/beeline.gif'>
                                                                    <img border='0' src='/img/visa.gif'>
                                                                    <img border='0' src='/img/mastercard.gif'>
                                                                    <!-- begin WebMoney Transfer : attestation label -->
                                                                    <a href='https://passport.webmoney.ru/asp/certview.asp?wmid='$wmid target=_blank>
                                                                    <IMG SRC='http://www.webmoney.ru/img/icons/88x31_wm_v_blue_on_white_ru.png'
                                                                    title='Здесь находится аттестат нашего WM идентификатора $wmid' border='0'></a>
                                                                    <!-- end WebMoney Transfer : attestation label -->			  
                                                                    <br /> 
				           <span class='note'>Для оплаты нажмите «Оплатить», после чего Вы будете перенаправленны на сайт robokassa.ru. Где сможете подтвердить платеж и выбрать способ оплаты</span>
					  <br />
					  <span class='note'><sup>1</sup> Электронный ящик или e-mail нужен для подтверждения платежа. И для отправки вам ссылки на оплаченный ресурс.</span>
				    </form>
				    </div>";
        }

        $e->output(serialize($order));
    default:
        break;
}
//Конец плагина
?>