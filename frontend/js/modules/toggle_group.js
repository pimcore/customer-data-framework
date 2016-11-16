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
