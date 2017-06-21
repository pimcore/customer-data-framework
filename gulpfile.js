'use strict';

var gulp = require('gulp');
var backendToolkitPath = '../BackendToolkit';

// shortcut to link a BT file
function bt(path) {
    return backendToolkitPath + '/' + path;
}

var backendToolkit = require('../BackendToolkit/build/gulp-tasks');
backendToolkit(gulp, require('gulp-load-plugins')(), {
    frontend: {
        path: 'src/Resources/public/dist',

        files: {
            scripts: {
                'lib.js': [
                    bt('frontend/bower_components/jquery/dist/jquery.js'),
                    bt('frontend/bower_components/admin-lte/plugins/fastclick/fastclick.js'),
                    bt('frontend/bower_components/admin-lte/plugins/slimScroll/jquery.slimscroll.js'),
                    bt('frontend/bower_components/admin-lte/plugins/pace/pace.js'),
                    bt('frontend/bower_components/admin-lte/plugins/select2/select2.full.js'),
                    bt('frontend/bower_components/admin-lte/plugins/iCheck/icheck.js'),
                    bt('frontend/bower_components/admin-lte/plugins/daterangepicker/moment.js'),
                    bt('frontend/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js'),
                    bt('frontend/bower_components/admin-lte/bootstrap/js/bootstrap.js'),
                    bt('frontend/bower_components/admin-lte/dist/js/app.js')
                ],

                'script.js': [
                    bt('frontend/js/init.js'),
                    bt('frontend/js/util.js'),
                    bt('frontend/js/modules/**/*.js'),
                    bt('frontend/js/functions.js'),
                    'frontend/js/functions.js',
                    bt('frontend/js/script.js')
                ]
            },

            styles: {
                'lib.css': [
                    bt('frontend/bower_components/admin-lte/bootstrap/css/bootstrap.css'),
                    bt('frontend/bower_components/font-awesome/css/font-awesome.css'),
                    bt('frontend/bower_components/ionicons/css/ionicons.css'),
                    bt('frontend/bower_components/admin-lte/plugins/pace/pace.css'),
                    bt('frontend/bower_components/admin-lte/plugins/select2/select2.css'),
                    bt('frontend/bower_components/admin-lte/plugins/iCheck/all.css'),
                    bt('frontend/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.css'),
                    bt('frontend/bower_components/admin-lte/dist/css/AdminLTE.css'),
                    bt('frontend/bower_components/admin-lte/dist/css/skins/skin-blue.css')
                ],

                'style.css': {
                    options: {
                        pleeease: {
                            sass: true
                        }
                    },

                    files: [
                        'frontend/scss/style.scss'
                    ]
                }
            },

            assets: {
                'fonts': {
                    'files': [
                        bt('frontend/bower_components/font-awesome/fonts/*'),
                        bt('frontend/bower_components/ionicons/fonts/*'),
                        bt('frontend/bower_components/admin-lte/bootstrap/fonts/*')
                    ]
                },

                'iCheck': {
                    'dest': 'assets/admin-lte/plugins/iCheck',
                    'files': [
                        bt('frontend/bower_components/admin-lte/plugins/iCheck/**/*.png')
                    ]
                }
            }
        }
    }
});
