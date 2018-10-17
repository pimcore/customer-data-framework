$(function() {
    var customerExporter = {
        init: function() {
            $('.js-customer-export').on('click', function(e){
                e.preventDefault();

                $('#exportModal').modal({
                    backdrop: 'static',
                    keyboard: false
                });

                $.ajax({
                    url: $(this).data('href'),
                    success: function(data) {
                        if(data.url) {
                            customerExporter.processExportStep(data.url);
                        } else {
                            customerExporter.exportFailed();
                        }

                    },
                    error: customerExporter.exportFailed
                });
            });

            $('.js-customer-import').on('click', function(e){
                e.preventDefault();

                window.parent.customermanagementframeworkPlugin.startCustomerImport();
            });
        },

        processExportStep: function(url) {
            $.ajax({
                url: url,
                success: function(data) {
                    if(data.finished === false) {
                        $('#exportModal').find('.js-progress-label').html(data.progress);
                        $('#exportModal').find('.progress-bar').css('width', data.percent+'%').attr('aria-valuenow', data.percent);
                        customerExporter.processExportStep(url);
                    } else if(data.finished === true) {

                        setTimeout(function(){
                            $('#exportModal').modal('hide');
                            $('#exportModal').find('.js-progress-label').html('');
                            $('#exportModal').find('.progress-bar').css('width', '0').attr('aria-valuenow', 0);
                        }, 1000);

                        window.location = data.url;
                    } else {
                        customerExporter.exportFailed();
                    }
                },
                error: customerExporter.exportFailed
            });
        },

        exportFailed: function() {
            alert('Export failed');
            $('#exportModal').modal('hide');
        }
    };

    $('body').on('click','.pos a', function (event) {
        event.preventDefault();
        let url = $(this).attr("href");
        $('.customer-table-content').css('opacity',0.5);
        $.ajax({
            url: url,
            success: function(data) {
                $('.customer-table-content').html(data);
                $('.customer-table-content').css('opacity',1);
            },
            error: 'customer data list request failed'
        });
    });

    customerExporter.init();
}());