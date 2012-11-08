<?php

/**
 * Печатает поля для добавления вариантов и свойств при редактировании документов. 
 * @author SerovAlexander <serov.sh@gmail.com>
 */
$e = &$modx->Event;
$output = '';


#######################################################

if ($e->name == 'OnDocFormRender') {

    if (isset($_REQUEST['mshop']) && $_REQUEST['mshop'] == 1) {
        require_once MODX_BASE_PATH . 'assets/modules/shop/models/MShopModel.class.php';
        $mshop = new MShopModel($modx);
        global $content;

        if (isset($_POST['mshop_redirect']))
            $redirect = $_POST['mshop_redirect'];
        else
            $redirect = 'index.php?a=' . $_GET['last_a'] . '&id=' . $_GET['last_id'] . '&view=' . $_GET['last_view'] . '&mshop_pid=' . $_GET['mshop_pid'];
        $output.='<script type="text/javascript">
            

            
            var html = \'<br /><input type="text" name="mshop_parent" value="' . $content['mshop_parent'] . '" />\';  
            $j("#mutate input[name=parent]").after(html);            
            
                
                $j("#Button4 a").attr("onClick", "documentDirty=false;document.location.href=\'' . $redirect . '\';");
                
             
                $j("#Button3").remove();
                $j("#Button5").remove();
                $j("#Button6").remove();
            
                </script>';

        if (is_array($mshop->product_template)) {
            if (!in_array($content['template'], $mshop->product_template)) {
                $e->output($output);
                return '';
            }
        } else {
            if ($content['template'] != $mshop->product_template) {
                $e->output($output);
                return '';
            }
        }



        $output.='
<style type="text/css">
#mutate .mshop input.mshop {
  width: 100px;
}
</style>

<script type="text/javascript">
    

function addMShopVariant() {
   count = $j(".mshop_variants").size();
   count = count+1;
   var newRow = $j("<tr class=mshop_variants>   <td><input type=text class=mshop name=mshop_variant[new"+count+"][name]></td>   <td><input type=text class=mshop name=mshop_variant[new"+count+"][article]></td>   <td><input type=text class=mshop name=mshop_variant[new"+count+"][price]></td>   <td><input type=text class=mshop name=mshop_variant[new"+count+"][stock]></td><td><input type=text class=mshop name=mshop_variant[new"+count+"][unit]></td>    <td><a href=\'javascript:;\' onclick=\"$j(this).parents(\'tr\').remove();\">Удалить</a></td></tr>");
   $j(".mshop_variants:last").after(newRow);
    return false;
}

function removeMShopVariant(id_variant) {
    r = $j("#removeMShopVariant").val();
    $j("#removeMShopVariant").val(r+","+id_variant);
}
</script>

<div class="sectionHeader mshopr" style="margin: auto 20px;">Параметры магазина</div>'; //lang

        $output.='<div class="sectionBody mshop" style="margin: auto 20px;"> 
            <h3>Варианты товара</h3>
        <table id="MShopVariants"><tr class="mshop_variants">
        <th>Название</th>
        <th>Артикул</th>
        <th>Цена</th>
        <th>Склад</th>
        <th>Ед.Измерения</th>
        <th></th>
        </tr>    
        '; //lang

        if (isset($_REQUEST['mshop_id']) && is_numeric($_REQUEST['mshop_id'])) {
            $variants = $mshop->document->getVariants($_REQUEST['mshop_id']);
            foreach ($variants as $variant) {
                $output.='<tr class="mshop_variants"><td><input type="text" class="mshop" name="mshop_variant[' . $variant['id'] . '][name]" value="' . $variant['name'] . '"></td>';
                $output.='<td><input class="mshop" type="text" name="mshop_variant[' . $variant['id'] . '][article]" value="' . $variant['article'] . '"></td>';
                $output.='<td><input class="mshop" type="text" name="mshop_variant[' . $variant['id'] . '][price]" value="' . $variant['price'] . '"> </td>';
                $output.='<td><input class="mshop" type="text" name="mshop_variant[' . $variant['id'] . '][stock]" value="' . $variant['stock'] . '"> </td>';
                $output.='<td><input class="mshop" type="text" name="mshop_variant[' . $variant['id'] . '][unit]" value="' . $variant['unit'] . '"> </td>';
                $output.='<td><a href="javascript:;" onclick="$j(this).parents(\'tr\').remove();removeMShopVariant(' . $variant['id'] . ');">Удалить</a></td></tr>'; //lang
            }
        } else {
            $output.='<tr class="mshop_variants"><td><input type="text" class="mshop" name="mshop_variant[new1][name]" value=""></td>';
            $output.='<td><input class="mshop" type="text" name="mshop_variant[new1][article]" value=""></td>';
            $output.='<td><input class="mshop" type="text" name="mshop_variant[new1][price]" value=""> </td>';
            $output.='<td><input class="mshop" type="text" name="mshop_variant[new1][stock]" value=""> </td>';
            $output.='<td><input class="mshop" type="text" name="mshop_variant[new1][unit]" value=""> </td>';
            $output.='<td><a href="javascript:;" onclick="$j(this).parents(\'tr\').remove();">Удалить</a></td></tr>'; //lang
        }

        $output.='</table> 
            <input type="hidden" id="removeMShopVariant" name="remove_mshop_variants" value="0">
            <a href="javascript:;" onclick="addMShopVariant();">Добавить вариант</a>'; //lang

        $brands = $mshop->brand->getBrands();
        $output.='<br/> <label>Бренд:<select name="mshop_brand">';
        $selected = '';
        if (is_array($brands)) {
            foreach ($brands as $b) {
                if (isset($content['id_brand']) && $content['id_brand'] == $b['id'])
                    $selected = 'selected';
                $output.='<option value="' . $b['id'] . '" ' . $selected . '>' . $b['pagetitle'] . '</option>';
                $selected = '';
            }
        }
        $output.='</select></label>';

        $output.='<h2>Свойства</h2>';
        $prs = $mshop->property->getProperties($content['mshop_parent']);


        foreach ($prs as $pr) {
            $p = $pr['property'];
            if (empty($p['options'])) {
                $value = isset($content['mshop_properies'][$p['id']]) ? $content['mshop_properies'][$p['id']] : '';
                $output.='<label>' . $p['name'] . ': <input type="text" name="mshop_properties[' . $p['id'] . ']" value="' . $value . '"></label> <br/>';
            } elseif (is_array($p['options'])) {
                $output.='<label>' . $p['name'] . ':<select name="mshop_properties[' . $p['id'] . ']">';
                $selected = '';
                foreach ($p['options'] as $o) {
                    if (isset($content['mshop_properies'][$p['id']]) && $content['mshop_properies'][$p['id']] == $o)
                        $selected = 'selected';
                    $output.='<option value="' . $o . '" ' . $selected . '>' . $o . '</option>';
                    $selected = '';
                }
                $output.='</select></label><br/>';
            }
        }


        $output.='</div>';
    }
}

#######################################################

$e->output($output);
$modx->clearCache();
?>