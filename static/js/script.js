// Config init...
var _cfg = _config || {};

var cls = {
    log : function( _output ){
        if( app.debug ){
            console.log(_output);
        }
    }
};

var app = {
    debug: false,
    timeoutHandler: [],
    TWITTER: false,
    FACEBOOK: false,
    GPLUS: false,
    PINTEREST: false,

    DEVICE : '',

    //-> initialize function
    _init : function(){
        cls.log('-> _init');

        //set Device for AjaxInclude
        if( matchMedia('(max-width:767px)').matches ){
            app.DEVICE = 'mobile';
        }


    }, // end init()


    formAutoSubmit: function(){

        $('.js-form-auto-submit').on('change', function(){
            $(this).closest('form').submit();
        });
    }



};
window.app = app || {};


;(function( $ ){
    "use strict";

    /* -> _config._preload = Load this functions first */
    if (_cfg['_preload']) {
        $.each( _cfg['_preload'], function( _key, _val ){
            if( typeof _val == 'boolean' && typeof window.app[_key] == 'function' ){
                window.app[_key]();
            }
        });
    }

    /* -> _config = Load all others (not _preload and _reload) */
    $.each( _cfg, function( _key, _val ){
        if( ( typeof _val == 'boolean' && typeof window.app[_key] == 'function' && _key != '_reload' && _key != '_preload' ) ){
            window.app[_key]();
        }
    });

    /* -> _config._reload = Load the ajaxInclued and others after the rest */
    if (_cfg['_reload']) {
        $.each( _cfg['_reload'], function( _key, _val ){
            if( ( typeof _val == 'boolean' && typeof window.app[_key] == 'function' ) ){
                window.app[_key]();
            }
        });
    }

})(jQuery);