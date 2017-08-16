'use strict';

var gulp = require('gulp');
var gulpTasks = require('./frontend/build/gulp-tasks');

gulpTasks(gulp, require('gulp-load-plugins')(), {
    frontend: {
        path: 'src/Resources/public/admin',

        files: {
            scripts: {
                'lib.js': [
                    'node_modules/jquery/dist/jquery.js',
                    'node_modules/admin-lte/plugins/fastclick/fastclick.js',
                    'node_modules/admin-lte/plugins/slimScroll/jquery.slimscroll.js',
                    'node_modules/admin-lte/plugins/pace/pace.js',
                    'node_modules/admin-lte/plugins/select2/select2.full.js',
                    'node_modules/admin-lte/plugins/iCheck/icheck.js',
                    'node_modules/admin-lte/plugins/daterangepicker/moment.js',
                    'node_modules/admin-lte/plugins/daterangepicker/daterangepicker.js',
                    'node_modules/admin-lte/bootstrap/js/bootstrap.js',
                    'node_modules/admin-lte/dist/js/app.js'
                ],
                'cmf.js': [
                    'frontend/js/init.js',
                    'frontend/js/util.js',
                    'frontend/js/modules/**/*.js',
                    'frontend/js/admin-lte-functions.js',
                    'frontend/js/functions.js',
                    'frontend/js/script.js'
                ]
            },

            styles: {
                'lib.css': [
                    'node_modules/admin-lte/bootstrap/css/bootstrap.css',
                    'node_modules/font-awesome/css/font-awesome.css',
                    'node_modules/ionicons/css/ionicons.css',
                    'node_modules/admin-lte/plugins/pace/pace.css',
                    'node_modules/admin-lte/plugins/select2/select2.css',
                    'node_modules/admin-lte/plugins/iCheck/all.css',
                    'node_modules/admin-lte/plugins/daterangepicker/daterangepicker.css',
                    'node_modules/admin-lte/dist/css/AdminLTE.css',
                    'node_modules/admin-lte/dist/css/skins/skin-blue.css'
                ],
                'cmf.css': {
                    options: {
                        pleeease: {
                            sass: {
                                includePaths: ['frontend/scss']
                            }
                        }
                    },

                    files: [
                        'frontend/scss/cmf.scss'
                    ]
                }
            },

            assets: {
                'fonts': {
                    'files': [
                        'node_modules/font-awesome/fonts/*',
                        'node_modules/ionicons/fonts/*',
                        'node_modules/admin-lte/bootstrap/fonts/*'
                    ]
                },
                'iCheck': {
                    'dest': 'assets/admin-lte/plugins/iCheck',
                    'files': [
                        'node_modules/admin-lte/plugins/iCheck/**/*.png'
                    ]
                }
            }
        }
    }
});
