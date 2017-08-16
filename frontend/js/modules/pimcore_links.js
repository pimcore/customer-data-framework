app.PimcoreLinks = (function () {
    'use strict';

    return {
        /**
         * Initialize for all pimcore links
         * @param $scope
         */
        initialize: function ($scope) {
            var that = this;

            $scope.find('.js-pimcore-link').on('click', function (e) {
                var objectId = $(this).data('pimcore-id');

                if (that.isPimcoreAvailable()) {
                    window.top.pimcore.helpers.openObject(objectId, 'object');
                } else {
                    app.Logger.error(
                        'Pimcore is not available (e.g. backend opened outside iframe) - can\'t load object with ID',
                        objectId
                    );
                }
            });
        },

        /**
         * Check if pimcore object is available (we're inside iframe)
         * @returns {boolean}
         */
        isPimcoreAvailable: function () {
            return 'undefined' !== typeof window.top.pimcore;
        }
    };
}());
