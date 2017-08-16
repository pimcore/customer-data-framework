var _cfg = _config || {};

var cls = {
    log : function( _output ){
        if( app.debug ){
            console.log(_output);
        }
    }
};

var app = {
    debug: _cfg.debug || false,
    timeoutHandler: [],
    DEVICE : ''
};

window.app = app || {};
