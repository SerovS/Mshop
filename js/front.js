
function getSiteUrl() {
    var site_url = jQuery('base').size()>0
    ? jQuery('base:first').attr('href')
    : window.location.protocol+'//'+window.location.host+'/';
    return site_url;
}

/**
 * Добавление товара в корзину. Или изменение кол-ва товара.
 * obj указатель на ссылку
 * id_var ID варианта цены.
 * count Кол-во товара
 */
function addCart(obj, id_var, count) { 
    if(id_var) {
        params = "&MShop_variant="+id_var;
    }else {
        params = getParams(obj);
    }                   
    
    if(count)
        params = params+"&MShop_count="+count;    
    toAjax("ajax=MShop&MShop_action=add"+params, "refreshCart(1)");
}

/**
 * Удаление товара из корзины
 * obj указатель на ссылку
 * id_var ID варианта цены.
 */
function deleteCart(obj, id_var) { 
    if(id_var) {
        params = "&MShop_variant="+id_var;
    }else {
        params = getParams(obj);
    }                   
    toAjax("ajax=MShop&MShop_action=delete"+params, "refreshCart(1)");
}

/**
 * Очистка корзины
 */
function emptyCart() {     
    toAjax("ajax=MShop&MShop_action=empty", "refreshCart(1)");
}

/**
 * Обновление корзины. 
 * get_html получать ли html корзины поумолчанию:1
 * run_func функция выполняющаяся после получения результатов поумолчанию:viewCart(data)
 */
function refreshCart(get_html, run_func) {
    var params="";
    if(get_html==1)
        params="&MShop_get_cart_html=1";
    
    params = params+"&MShop_cart_tpl="+cart_tpl+
    "&MShop_products_tpl="+products_tpl+
    "&MShop_empty_cart_tpl="+empty_cart_tpl;    

    if(!run_func)
        run_func = "viewCart(data)";
    
    toAjax("ajax=MShop&MShop_action=refresh"+params, run_func);
}

/**
 *  Отправка запроса серверу. 
 *  params параметры запроса
 *  successFunction функция выполняющаяся в случае успеха
 */
function toAjax(params, successFunction) {
    if(site_url=='undefined')
        site_url = getSiteUrl();
    $.ajax({
        type: "POST",
        dataType: "json",
        url: site_url + "assets/modules/shop/ajax.php",
        data: params,
        success: function(data){
            if(data.status=="ok")
                eval(successFunction);
            if(data.status=="error")
                $('#'+mshop_id).html(data.error);
        }
    });    
}

function getParams(obj) {
    var params = "";   
    $(obj).parents("form").find('input').each(function() {
        params = params + "&" + $(this).attr("name") +"="+ $(this).val() + "";        
    });
    $(obj).parents("form").find('select').each(function() {
        params = params + "&" + $(this).attr("name") +"="+ $(this).val() + "";        
    });    
    return params;
}

function viewCart(data) {
    $('#'+mshop_id).html(data.result.cart_html);
//alert(data.result.total);
}

function emptyF(data) {
    
}

/**
 * Добавление товара через хелпер
 * id_var ID варианта товара.
 * count количество
 */
function addCartHelper(id_var, count) { 
    
    params = "&MShop_variant="+id_var+"&MShop_count="+count;    
    toAjax("ajax=MShop&MShop_action=add"+params, "refreshCart(1)");
}

/**
*  Установка первоночальных значений хелпера
*
*/
function setNull(){				
    $('input[name="count"]').attr('value',1);
}

function hideHelper(){
    $('#MShopHelper').hide(); 
    setNull();
}


function downHelper(){
    var cur_value = $('input[name="count"]').attr('value');
    if(cur_value!=1)
    {
        cur_value = parseInt(cur_value) - 1;
        $('input[name="count"]').attr('value',cur_value);
    }
    return false;
}

function upHelper(){
    var cur_value = $('input[name="count"]').attr('value');
    cur_value = parseInt(cur_value) + 1;
    $('input[name="count"]').attr('value',cur_value); 
    return false;
}

function sendHelper(obj){
    var cou=$('#MShop_count').attr('value');	
    addCart(obj, false, cou);
    $(obj).parents('form').append($('#MShopHelper2'));	
    $('#MShopHelper2').show();
    hideHelper();
    return false;
}

function hideHelper2() {
    $("#MShopHelper2").hide(); 
}


