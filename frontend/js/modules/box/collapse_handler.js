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
