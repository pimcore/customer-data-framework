;(function ($) {
    'use strict';

    // generic admin-lte functions (e.g. selects, collapsed boxes, ...)
    $.extend(window.app, {
        /**
         * Initialize function
         */
        _init: function ($scope) {
            //set Device for AjaxInclude
            if (matchMedia('(max-width:767px)').matches) {
                app.DEVICE = 'mobile';
            }
        },

        /**
         * Auto submit forms on change
         * @param $scope
         */
        formAutoSubmit: function ($scope) {
            $scope.find('.js-form-auto-submit').on('change', function () {
                $(this).closest('form').submit();
            });
        },

        /**
         * Initialize select2 on .plugin-select2 elements
         * @param $scope
         */
        select2: function ($scope) {
            $scope.find(':input.plugin-select2').each(function () {
                var $element = $(this);

                // read placeholder from data-placeholder attribute
                var placeholder = '';
                if ($element.data('placeholder')) {
                    placeholder = $element.data('placeholder');
                }

                var options = $element.data('select2-options') || {};
                options = $.extend(true, {}, {
                    width: '100%',
                    allowClear: true,
                    placeholder: placeholder
                }, options);

                $element
                    .select2(options)
                    .focus(function () {
                        // workaround for clicking on label not triggering item focus (should be fixed upstream with 4.0.3)
                        $(this).select2('open');
                    });
            });
        },

        /**
         * Initialize iCheck in .plugin-icheck elements
         * @param $scope
         */
        iCheck: function ($scope) {
            $scope.find('.plugin-icheck :input').iCheck({
                checkboxClass: 'icheckbox_flat-blue',
                radioClass: 'iradio_flat-blue'
            });
        },

        /**
         * Initialize tooltips
         * @param $scope
         */
        tooltip: function ($scope) {
            $scope.find('.tooltip-trigger').tooltip();
        },

        /**
         * Search filter form handling
         * @param $scope
         */
        searchFilter: function ($scope) {
            $scope.find('.search-filters').each(function () {
                var $container = $(this);

                app.Util.getOrCreateInstance(
                    $container, 'SearchFilter.Form',
                    function ($el) {
                        return new app.SearchFilter.Form($el);
                    }
                );
            });
        },

        /**
         * Set up persistent collapse state saved to local storage
         * @param $scope
         */
        collapsibleStateBox: function ($scope) {
            $scope.find('.box-collapsible-state').each(function () {
                var $container = $(this);
                if ($container.data('identifier')) {
                    app.Util.getOrCreateInstance(
                        $container, 'Box.CollapseHandler',
                        function ($el) {
                            return app.Box.CollapseHandler($el);
                        }
                    );
                }
            });
        },

        /**
         * Reload page when item count changes
         * @param $scope
         */
        paginationFooterCount: function ($scope) {
            $scope.find('.pagination-footer__count-selector-form').find('select').on('change', function (e) {
                var $selectedOption = $(this).find('option:selected');
                if ($selectedOption.data('url')) {
                    window.location = $selectedOption.data('url');
                }
            });
        },

        /**
         * Collapse tables when .collapse-trigger caption is clicked
         * @param $scope
         */
        tableCollapse: function ($scope) {
            $scope.find('.table-collapsible').each(function () {
                var $table = $(this);
                var $caption = $table.find('caption');

                $('<span class="collapse-indicator" />')
                    .append('<span class="collapse-indicator-icon fa fa-chevron-down" />')
                    .prependTo($caption);

                $caption.on('click', function (e) {
                    $table.toggleClass('table-collapsible--collapsed');
                });
            });
        },

        /**
         * URL select loading option URL on change
         * @param $scope
         */
        urlSelect: function ($scope) {
            $scope.find('select.url-select').on('change', function (e) {
                e.preventDefault();

                var selectedOption = $(this).find('option:selected');
                if (selectedOption.length === 1) {
                    window.location.href = selectedOption.data('url');
                }
            });
        },

        /**
         * Modals
         * @param $scope
         */
        modal: function ($scope) {
            $scope.on('hidden.bs.modal', '.modal', function () {
                $(this).removeData('bs.modal');
            });
        },

        /**
         * Links to pimcore objects
         * @param $scope
         */
        pimcoreLink: function ($scope) {
            app.PimcoreLinks.initialize($scope);
        },

        /**
         * Toggle groups (order comment edit)
         * @param $scope
         */
        toggleGroup: function ($scope) {
            app.ToggleGroup.initialize($scope);
        }
    });
})(jQuery);
