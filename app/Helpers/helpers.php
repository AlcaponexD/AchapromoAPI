<?php

/**
 * Created by PhpStorm.
 * User: AlcaponexD
 * Date: 23/05/2021
 * Time: 09:35
 */
class helpers
{
    /*
     * Retira R$ do dinheiro antes de ir no banco
     */
    public static function money_to_db($money){
        if($money != 'empty'){
            $money_new =  str_replace('R$','',$money);
            $thousend =  str_replace('.','',$money_new);
            return   str_replace(',','.',$thousend);

        }else{
            return 0;
        }

    }
    /*
     * Formata o titilo do padrao pelando para comoum
     */
    public static function format_title_pelando($title){
        $arr_title = explode('|',$title);
        return $arr_title[0];
    }

    public static function update_links($link){
        //Amazon
        if(strpos($link,'amazon')){
            $link = explode('?',$link);
            $link = $link[0];
        }
        //Amazon
        if(strpos($link,'amazon')){
            $link = explode('/ref=',$link);
            $link = $link[0];
        }
        //submarino
        if(strpos($link,'submarino')){
            $link = explode('?',$link);
            $link = $link[0];
        }
        //americanas
        if(strpos($link,'americanas')){
            $link = explode('?',$link);
            $link = $link[0];
        }
        //soubarato
        if(strpos($link,'soubarato')){
            $link = explode('?',$link);
            $link = $link[0];
        }
        //kabum
        if(strpos($link,'kabum')) {
            if(strpos($link,'cgi-local')){
                $link = explode('&', $link);
            }else{
                $link = explode('?', $link);
            }

            $link = $link[0];
        }
        //aliexpress
        if(strpos($link,'aliexpress')){
            $link = explode('?',$link);
            $link = $link[0];
        }
        //extra
        if(strpos($link,'extra')){
            $link = explode('?',$link);
            $link = $link[0];
        }
        //shoptime
        if(strpos($link,'shoptime')){
            $link = explode('?',$link);
            $link = $link[0];
        }
        //pontofrio
        if(strpos($link,'pontofrio')){
            $link = explode('?',$link);
            $link = $link[0];
        }
        //netshoes
        if(strpos($link,'netshoes')){
            $link = explode('?',$link);
            $link = $link[0];
        }
        //terabyte
        if(strpos($link,'terabyteshop')){
            $link = explode('?',$link);
            $link = $link[0];
        }



        return $link;
    }
}