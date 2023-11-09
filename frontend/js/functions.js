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
        },
        registerSaveFilterDefinition: function () {
            $('#save-filter-definition').on('click', function (e) {
                e.preventDefault();
                var $input = $('input[name="filterDefinition[name]"]');
                var $requiredMessage = $('#name-required-message');
                if ($($input).val().length < 1) {
                    $input.focus();
                    $requiredMessage.slideDown();
                    setTimeout(function () {
                        $requiredMessage.slideUp();
                    }, 3000);
                    return;
                } else $requiredMessage.hide();
                var $form = $(this).closest("form");
                var originalAction = $form.attr('action');
                var $disabledSelects = $form.find('select:disabled');
                $disabledSelects.each(function(){
                    $(this).prop('disabled', false);
                });
                $form.attr('action', '/admin/customermanagementframework/customers/filter-definition/save').submit();
                $form.attr('action', originalAction);
                $disabledSelects.each(function(){
                    $(this).prop('disabled', true);
                });
            });
        },
        registerUpdateFilterDefinition: function () {
            $('#update-filter-definition').on('click', function (e) {
                e.preventDefault();
                var $input = $('input[name="filterDefinition[name]"]');
                var $requiredMessage = $('#name-required-message');
                if ($($input).val().length < 1) {
                    $input.focus();
                    $requiredMessage.slideDown();
                    setTimeout(function () {
                        $requiredMessage.slideUp();
                    }, 3000);
                    return;
                } else $requiredMessage.hide();
                var $form = $(this).closest("form");
                var originalAction = $form.attr('action');
                var $disabledSelects = $form.find('select:disabled');
                $disabledSelects.each(function(){
                    $(this).prop('disabled', false);
                });
                $form.attr('action', '/admin/customermanagementframework/customers/filter-definition/update').submit();
                $form.attr('action', originalAction);
                $disabledSelects.each(function(){
                    $(this).prop('disabled', true);
                });
            });
        },
        registerShareFilterDefinition: function () {
            $('#share-filter-definition').on('click', function (e) {
                e.preventDefault();
                var $form = $(this).closest("form");
                var originalAction = $form.attr('action');
                $form.attr('action', '/admin/customermanagementframework/customers/filter-definition/share').submit();
                $form.attr('action', originalAction);
            });
        },
        registerNewCustomerAction: function () {
            var $newCustomerButton = $('#add-new-customer');
            var isPimcoreAvailable = ('undefined' !== typeof window.top.pimcore);
            if(!isPimcoreAvailable) $newCustomerButton.hide();
            $newCustomerButton.on('click', function (e) {
                if (!isPimcoreAvailable) {
                    app.Logger.error(
                        'Pimcore is not available (e.g. backend opened outside iframe) - can\'t load object with ID',
                        objectId
                    );
                    return false;
                }
                $.ajax({
                    method : 'POST',
                    url: '/admin/customermanagementframework/customers/new',
                    headers: {
                        'X-Pimcore-Csrf-Token': $newCustomerButton.data('token')
                    },
                    success: function (data) {
                        var objectId = data.id;
                        if ('undefined' !== typeof window.top.pimcore) {
                            window.top.pimcore.helpers.openObject(objectId, 'object');
                        } else {
                            app.Logger.error(
                                'Pimcore is not available (e.g. backend opened outside iframe) - can\'t load object with ID',
                                objectId
                            );
                        }
                    }
                });
            });
        }
    });
})(jQuery);
