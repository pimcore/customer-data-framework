;(function ($) {
    'use strict';

    app.DeclineDuplicates = (function () {
        if($('.js-decline-duplicate').length) {
            $('body').on('click', '.js-decline-duplicate', function() {
                var $duplicateItem = $(this).closest('.js-duplicates-item');
                $duplicateItem.css("opacity", 0.5);

                $.ajax({
                    url: '/plugin/CustomerManagementFramework/duplicates/decline-duplicate?id=' + $(this).data('id'),
                    success: function(data) {
                        if(data.success) {
                            $duplicateItem.remove();
                        } else {
                            $duplicateItem.css("opacity", 1);
                        }
                    }

                });
            });
        }
    }());
})(jQuery);
