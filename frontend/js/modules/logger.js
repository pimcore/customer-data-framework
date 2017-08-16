app.Logger = (function() {
    var logger = {
        // log even if environemtn is not development
        forceLog: false
    };

    var canLog = function () {
        if (!window.console) {
            return false;
        }

        if (logger.forceLog) {
            return true;
        }

        return !!app.debug;
    };

    var makeLogger = function (type) {
        return function () {
            if (canLog()) {
                window.console[type].apply(logger, Array.prototype.slice.call(arguments));
            }
        };
    };

    logger.canLog = canLog;
    $.each(['error', 'warn', 'info', 'debug', 'log'], function (idx, type) {
        logger[type] = makeLogger(type);
    });

    return logger;
}());
