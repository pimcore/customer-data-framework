app.Util = (function() {
    'use strict';

    return {
        getOrCreateInstance: function ($element, identifier, factory) {
            if (!$element.data(identifier)) {
                $element.data(identifier, factory($element));
            }

            return $element.data(identifier);
        },

        featureDetect: (function () {
            var results = {};
            var tests = {
                // taken from modernizr
                localStorage: function () {
                    var mod = 'test';

                    try {
                        localStorage.setItem(mod, mod);
                        localStorage.removeItem(mod);
                        return true;
                    } catch (e) {
                        return false;
                    }
                },

                json: function () {
                    return 'JSON' in window && 'parse' in JSON && 'stringify' in JSON;
                }
            };

            return function (type) {
                if ('undefined' === typeof tests[type]) {
                    throw new Error('Test ' + type + ' is not defined');
                }

                if ('undefined' === typeof results[type]) {
                    results[type] = tests[type].call();
                }

                return results[type];
            };
        }())
    }
}());
