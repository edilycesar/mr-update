<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Src;

/**
 * Description of UrlTools
 *
 * @author edily
 */
class UrlTools {
    
    public static function getLastName($path) 
    {
        //app/media/dados/htdocs/edily/emissor-nfe/app
        $cols = explode("/", $path);
        return $cols[count($cols) - 1];
    }
    
    public static function stripDoubleBars($path) 
    {
        while (strpos($path, "//") !== false) {
            $path = str_replace("//", "/", $path);
        }
        return $path;
    }
    
    public static function removeLastFile($path) 
    {
        $path = self::stripDoubleBars($path);
        
        $cols = explode("/", $path);
        $c = count($cols);
        $path2 = "";
        for ($i=0; $i<$c-1; $i++) {
            $path2 .= $cols[$i] . "/";
        }
        return $path2;
    }
}
