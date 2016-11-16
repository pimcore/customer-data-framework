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
