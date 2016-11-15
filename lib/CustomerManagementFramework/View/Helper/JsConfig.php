<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 11:15
 */

namespace CustomerManagementFramework\View\Helper;

class JsConfig extends \Zend_View_Helper_Abstract{

    protected static $config = [ ];

    protected static $jsVariable = [ "_config" ];
    protected static $jsVariableName = "_config";

    public function jsConfig( $jsVariableName = "_config" ){
        if( !in_array( $jsVariableName, self::$jsVariable ) ){
            self::$jsVariable[] = $jsVariableName;
        }
        self::$jsVariableName = $jsVariableName;
        return $this;
    }

    public function add( $key, $value = '' ){
        if( is_array( $key ) ){
            if( is_array(self::$config[self::$jsVariableName]) ){
                self::$config[self::$jsVariableName] = array_merge( self::$config[self::$jsVariableName], $key );
            }else{
                self::$config[self::$jsVariableName] = $key;
            }
        }else{
            self::$config[self::$jsVariableName][ $key ] = $value;
        }
    }

    public function __toString(){
        $config = "<script>\n";
        foreach(self::$jsVariable as $index => $varKey){
            if( count( self::$config[$varKey] ) > 0 ){
                $values = self::$config[$varKey];
            }else{
                $values = new \stdClass();
            }
            $config .= "\tvar " . $varKey . " = " . json_encode( $values ) . ";";
            $config .= "\n";
        }
        $config .= "\n";
        $config .= "</script>\n";

        return $config;
    }

}