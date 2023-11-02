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

app.ToggleGroup = (function() {
    return {
        initialize: function ($scope) {
            $scope.find('[data-toggle-group-trigger]').each(function() {
                var $trigger = $(this);
                var group = $trigger.data('toggle-group-trigger');
                var $groups = $trigger.closest('.js-toggle-group-container').find('[data-toggle-group="' + group + '"]');

                $groups.hide().removeClass('hide');
                $groups.first().show();

                $trigger.on('click', function(e) {
                    e.preventDefault();
                    $groups.toggle();
                });
            });
        }
    }
}());

if ('undefined' === typeof app.Box) {
    app.Box = {};
}

app.Box.CollapseHandler = (function () {
    'use strict';

    return function ($box) {
        var storageKey = 'collapseState';
        var collapseClass = 'collapsed-box';
        var identifier = $box.data('identifier');
        var $collapseWidget = $box.find('[data-widget="collapse"]');
        var $icon = $box.find('[data-widget="collapse"] i');

        var StateStorage = {
            canHandleState: (function () {
                if (!identifier) {
                    return false;
                }

                var result = true;
                $.each(['localStorage', 'json'], function (index, feature) {
                    if (!app.Util.featureDetect(feature)) {
                        result = false;
                    }
                });

                return result;
            }()),

            load: function () {
                var state = localStorage.getItem(storageKey);
                if (null !== state) {
                    state = JSON.parse(state);
                } else {
                    state = {};
                }

                return state;
            },

            save: function (state) {
                localStorage.setItem(storageKey, JSON.stringify(state));
            }
        };

        var Box = {
            isCollapsed: function () {
                return $box.hasClass(collapseClass);
            },

            collapse: function (state) {

                if (state) {
                    $box.addClass(collapseClass);
                } else {
                    $box.removeClass(collapseClass);
                }

                Box.updateCollapseIcon(state);
            },

            updateCollapseIcon: function(state)
            {
                var iconClass;

                if (state) {
                    iconClass = 'fa-plus';
                } else {
                    iconClass = 'fa-minus';
                }

                $icon.attr('class', 'fa ' + iconClass);
            },

            handleState: function () {
                var state = this.loadState();
                if (state && !this.isCollapsed()) {
                    this.collapse(true);
                }
            },

            loadState: function () {
                if (!StateStorage.canHandleState) {
                    return;
                }

                var storage = StateStorage.load();

                var state = false;
                if ('undefined' !== typeof storage[identifier] && storage[identifier]) {
                    state = true;
                }

                return state;
            },

            saveState: function (state) {
                state = !!state;

                if (!StateStorage.canHandleState) {
                    return;
                }

                var storage = StateStorage.load();
                if ('undefined' === typeof storage[identifier]) {
                    storage[identifier] = {};
                }

                storage[identifier] = state;

                StateStorage.save(storage);
            }
        };

        // save collapsed state when widget is clicked
        if (StateStorage.canHandleState) {
            $collapseWidget.on('click', function (e) {
                // negate state as it will be changed after this handler runs
                Box.saveState(!Box.isCollapsed());
                Box.updateCollapseIcon(!Box.isCollapsed());
            });
        }

        // trigger box collapse from other elements, but keep updating collapse icon
        $box.find('[data-widget="collapse-trigger"]').on('click', function (e) {
            e.preventDefault();
            $collapseWidget.trigger('click');
        });

        Box.handleState();

        return Box;
    };
}());

if ('undefined' === typeof app.SearchFilter) {
    app.SearchFilter = {};
}

app.SearchFilter.Form = (function () {
    'use strict';

    var Form = function ($form) {
        this.$form = $form;

        this.setupFormSubmitHandler();
        this.setupDateRangePickers();
    };

    Form.prototype.setupFormSubmitHandler = function () {
        var that = this;

        // do not submit empty filter values (only submits filters which have values)
        this.$form.on('submit', function (e) {
            that.prepareFormSubmit();

            // no filters left -> load same URL without query string (no filters)
            if (that.$form.serializeArray().length === 0) {
                e.preventDefault();
                window.location = window.location.href.split('?')[0];
            }
        });
    };

    /**
     * Setup date range pickers and add hidden inputs with start/end values on change
     */
    Form.prototype.setupDateRangePickers = function () {
        var that = this;
        this.dateRangePickers = [];

        this.$form.find(':input.plugin-daterangepicker').each(function () {
            var $input = $(this);

            // handle update event and add hidden fields with from/to values to form
            $input.on('update.DateRangePicker', function (e, pickerResult) {
                var baseName = $input.attr('name').match(/^filter\[(.+)\]$/);
                if (null === baseName) {
                    app.Logger.error('Could not resolve base name for date range input', $input.attr('name'));
                    return;
                }

                baseName = baseName[1];

                $.each(['from', 'to'], function (index, type) {
                    var value = '';
                    if (pickerResult[type]) {
                        value = pickerResult[type].format('DD.MM.YYYY');
                    }

                    // orderDateFrom, orderDateTo
                    var inputName = baseName + type.charAt(0).toUpperCase() + type.slice(1)

                    that.updateHiddenField('filter[' + inputName + ']', value);
                });
            });

            // initialize date picker instance
            var dateRangePicker = app.Util.getOrCreateInstance(
                $(this), 'SearchFilter.DateRangePicker',
                function ($el) {
                    return new app.SearchFilter.DateRangePicker($el);
                }
            );

            that.dateRangePickers.push(dateRangePicker);
        });
    };

    /**
     * Add/update/remove hidden field from form
     *
     * @param name
     * @param value
     */
    Form.prototype.updateHiddenField = function (name, value) {
        var field = this.$form.find(':input[type="hidden"][name="' + name + '"]');
        value = $.trim(value);

        if (field.length === 0 && value.length > 0) {
            field = $('<input />')
                .attr('type', 'hidden')
                .attr('name', name);

            field.appendTo(this.$form);
        }

        // remove hidden field without value
        if (field.length === 1 && value.length === 0) {
            field.remove();
        } else {
            field.val(value);
        }
    };

    /**
     * Disable empty form fields before submitting form
     */
    Form.prototype.prepareFormSubmit = function () {
        var disableInput = function (input) {
            input.data('before-form-prepare-state', input.prop('disabled'));
            input.prop('disabled', true);
        };

        this.$form.find(':input').each(function () {
            var $input = $(this);
            if ($input.hasClass('form-submit-disabled') || !$.trim($input.val())) {
                disableInput($input);
            }
        });
    };

    /**
     * Restore form field state after submitting form
     */
    Form.prototype.restoreFormFieldStates = function () {
        this.$form.find(':input').each(function () {
            var $input = $(this);

            var previousState = !!$input.data('before-form-prepare-state');
            if ($input.prop('disabled')) {
                $input.prop('disabled', previousState);
            }
        });
    };

    return Form;
}());

app.SearchFilter.DateRangePicker = (function () {
    'use strict';

    var DateRangePicker = function ($input) {
        this.$input = $input;

        // do not include input display value in form submit
        $input.addClass('form-submit-disabled');

        this.initDateRangePicker();
    };

    DateRangePicker.prototype.triggerUpdate = function () {
        var picker = this.$input.data('daterangepicker');

        this.$input.trigger('update.DateRangePicker', {
            from: picker.startDate,
            to: picker.endDate
        });
    };

    DateRangePicker.prototype.initDateRangePicker = function () {
        var that = this;
        var dates = this.buildInitialDates();
        var ranges = this.buildRanges();

        this.$input.daterangepicker({
            autoUpdateInput: true,
            autoApply: true,
            alwaysShowCalendars: true,
            showWeekNumbers: true,
            startDate: dates.startDate,
            endDate: dates.endDate,
            locale: {
                format: 'DD.MM.YYYY'
            },
            ranges: ranges
        });

        // trigger update event when picker changes
        this.$input.on('apply.daterangepicker', $.proxy(this.triggerUpdate, this));

        // trigger update event initally
        this.triggerUpdate();
    };

    DateRangePicker.prototype.buildRanges = function () {
        var ranges = {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        };

        // add ranges defined by data attribute
        var dataRanges = this.$input.data('date-ranges');
        if (dataRanges && 'object' === typeof dataRanges) {
            $.each(dataRanges, function(label, range) {
                ranges[label] = [
                    moment(range.startDate, 'DD.MM.YYYY'),
                    moment(range.endDate, 'DD.MM.YYYY')
                ];
            });
        }

        return ranges;
    };

    DateRangePicker.prototype.buildInitialDates = function () {
        var startDate;
        if (this.$input.data('date-from')) {
            startDate = moment(this.$input.data('date-from'), 'DD.MM.YYYY');
        } else {
            startDate = moment().startOf('month');
        }

        var endDate;
        if (this.$input.data('date-to')) {
            endDate = moment(this.$input.data('date-to'), 'DD.MM.YYYY');
        } else {
            if (startDate) {
                endDate = startDate.clone().endOf('month');
            } else {
                endDate = moment().startOf('month');
            }
        }

        return {
            startDate: startDate,
            endDate: endDate
        };
    };

    return DateRangePicker;
}());

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

;(function( $ ){
    "use strict";

    window.app.init = function ($scope) {
        /* -> _config._preload = Load this functions first */
        if (_cfg['_preload']) {
            $.each( _cfg['_preload'], function( _key, _val ){
                if( typeof _val == 'boolean' && typeof window.app[_key] == 'function' ){
                    window.app[_key]($scope);
                }
            });
        }

        /* -> _config = Load all others (not _preload and _reload) */
        $.each( _cfg, function( _key, _val ){
            if( ( typeof _val == 'boolean' && typeof window.app[_key] == 'function' && _key != '_reload' && _key != '_preload' ) ){
                window.app[_key]($scope);
            }
        });

        /* -> _config._reload = Load the ajaxInclued and others after the rest */
        if (_cfg['_reload']) {
            $.each( _cfg['_reload'], function( _key, _val ){
                if( ( typeof _val == 'boolean' && typeof window.app[_key] == 'function' ) ){
                    window.app[_key]($scope);
                }
            });
        }
    };

    $(document).ready(function() {
        window.app.init($('body'));
    });
})(jQuery);
