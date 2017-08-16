;(function ($) {
    'use strict';

    // cmf functions
    $.extend(window.app, {
        declineDuplicates: function ($scope) {
            if ($scope.find('.js-decline-duplicate').length === 0) {
                return;
            }

            $scope.on('click', '.js-decline-duplicate', function () {
                var $duplicateItem = $(this).closest('.js-duplicates-item');
                $duplicateItem.css("opacity", 0.5);

                $.ajax({
                    url: '/admin/customermanagementframework/duplicates/decline/' + $(this).data('id'),
                    success: function (data) {
                        if (data.success) {
                            $duplicateItem.remove();
                        } else {
                            $duplicateItem.css("opacity", 1);
                        }
                    }
                });
            });
        }
    });
})(jQuery);
