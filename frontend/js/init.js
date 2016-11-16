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
    DEVICE : ''
};

window.app = app || {};
