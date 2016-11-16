'use strict';

var _ = require('lodash');
var path = require('path');
var gulp = require('gulp');
var gulpLoadPlugins = require('gulp-load-plugins');

// IMPORTANT: the source folder for the frontend code is intentionally named "frontend" and NOT "static" as otherwise
// the pimcore .htaccess will kick in and allow HTTP access to the whole source folder. As we can't be sure what's included
// in the packages installed by bower (e.g. PHP sample files with potential security issues) and we want to serve only our
// built assets, we intentionally keep the source files in a directory which is never accessible via HTTP.
//
// Do NOT move the source files into a folder named static*!

var $ = gulpLoadPlugins();
var config = {
    frontend: {
        path: 'static/dist',

        files: {
            scripts: {
                'lib.js': [
                    'frontend/bower_components/jquery/dist/jquery.js',
                    'frontend/bower_components/admin-lte/plugins/fastclick/fastclick.js',
                    'frontend/bower_components/admin-lte/plugins/slimScroll/jquery.slimscroll.js',
                    'frontend/bower_components/admin-lte/plugins/pace/pace.js',
                    'frontend/bower_components/admin-lte/plugins/select2/select2.full.js',
                    'frontend/bower_components/admin-lte/plugins/iCheck/icheck.js',
                    'frontend/bower_components/admin-lte/plugins/daterangepicker/moment.js',
                    'frontend/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js',
                    'frontend/bower_components/admin-lte/bootstrap/js/bootstrap.js',
                    'frontend/bower_components/admin-lte/dist/js/app.js'
                ],

                'script.js': [
                    'frontend/js/init.js',
                    'frontend/js/util.js',
                    'frontend/js/modules/**/*.js',
                    'frontend/js/functions.js',
                    'frontend/js/script.js'
                ]
            },

            styles: {
                'lib.css': [
                    'frontend/bower_components/admin-lte/bootstrap/css/bootstrap.css',
                    'frontend/bower_components/font-awesome/css/font-awesome.css',
                    'frontend/bower_components/ionicons/css/ionicons.css',
                    'frontend/bower_components/admin-lte/plugins/pace/pace.css',
                    'frontend/bower_components/admin-lte/plugins/select2/select2.css',
                    'frontend/bower_components/admin-lte/plugins/iCheck/all.css',
                    'frontend/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.css',
                    'frontend/bower_components/admin-lte/dist/css/AdminLTE.css',
                    'frontend/bower_components/admin-lte/dist/css/skins/skin-blue.css'
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
                        'frontend/bower_components/font-awesome/fonts/*',
                        'frontend/bower_components/ionicons/fonts/*',
                        'frontend/bower_components/admin-lte/bootstrap/fonts/*'
                    ]
                },

                'iCheck': {
                    'dest': 'assets/admin-lte/plugins/iCheck',
                    'files': [
                        'frontend/bower_components/admin-lte/plugins/iCheck/**/*.png'
                    ]
                }
            }
        }
    }
};

/*
 Registers a clean, watch, build and build-clean task for every profile (e.g. backend). Exposes every type (e.g. scripts
 and every result file (e.g. scripts:b2b.js) as extra task which is called from aggregating tasks. Example structure:

 ├── b2b:clean
 ├── b2b:scripts:b2b.js
 ├─┬ b2b:scripts
 │ └── b2b:scripts:b2b.js
 ├── b2b:styles:b2b.css
 ├─┬ b2b:styles
 │ └── b2b:styles:b2b.css
 ├── b2b:watch
 ├── b2b:build
 ├─┬ b2b:build-clean
 │ └── b2b:clean
 ├── backend:clean
 ├── backend:scripts:lib.js
 ├── backend:scripts:backend.js
 ├─┬ backend:scripts
 │ ├── backend:scripts:lib.js
 │ └── backend:scripts:backend.js
 ├── backend:styles:lib.css
 ├── backend:styles:backend.css
 ├─┬ backend:styles
 │ ├── backend:styles:lib.css
 │ └── backend:styles:backend.css
 ├── backend:assets:fonts
 ├─┬ backend:assets
 │ └── backend:assets:fonts
 ├── backend:watch
 ├── backend:build
 ├─┬ backend:build-clean
 │ └── backend:clean
 ├── watch
 ├── build
 ├── build-clean
 └── default
 */
Object.keys(config).forEach(function (profile) {
    var distPath = config[profile].path;
    var profileTasks = [];
    var watchProfiles = [];

    function taskName(name) {
        return profile + ':' + name;
    }

    function registerTasks(type, files, callback) {
        var taskNames = [];

        Object.keys(files).forEach(function (filename) {
            var name = taskName(type + ':' + filename);
            taskNames.push(name);

            // config object can either be an array of files or an object defining files as sub-property
            // and having additional options in an options object
            var taskFiles = files[filename];
            var taskConfig = {};

            if (!Array.isArray(taskFiles) && taskFiles.hasOwnProperty('files')) {
                taskConfig = taskFiles;
                taskFiles = taskFiles.files;
            }

            // filename task
            gulp.task(name, function () {
                callback(filename, taskFiles, taskConfig);
            });

            watchProfiles.push({
                task: name,
                files: taskFiles
            });
        });

        // type task includes all filename tasks
        var typeTaskName = taskName(type);

        gulp.task(typeTaskName, taskNames);
        profileTasks.push(typeTaskName);

        return taskNames;
    }

    // clean task
    var cleanTaskName = taskName('clean');
    gulp.task(cleanTaskName, function () {
        return gulp.src(distPath, {read: false})
            .pipe($.clean());
    });

    if (config[profile].hasOwnProperty('files')) {
        var files = config[profile].files;

        // JS tasks
        if (files.hasOwnProperty('scripts')) {
            registerTasks('scripts', files.scripts, function (filename, taskFiles, taskConfig) {
                var destination = distPath + '/js';

                return gulp.src(taskFiles)
                // .pipe($.sourcemaps.init())
                    .pipe($.plumber())
                    .pipe($.concat(filename))
                    .pipe(gulp.dest(destination))
                    .pipe($.uglify())
                    .pipe($.rename({extname: '.min.js'}))
                    // .pipe($.sourcemaps.write())
                    .pipe(gulp.dest(destination));
            });
        }

        // CSS tasks
        if (files.hasOwnProperty('styles')) {
            registerTasks('styles', files.styles, function (filename, taskFiles, taskConfig) {
                var destination = distPath + '/css';

                // base paths to resolve imports correctly
                var paths = [process.cwd()];
                taskFiles.forEach(function (file) {
                    paths.push(path.dirname(file));
                });

                var pleeeaseOptions = {
                    minifier: false,
                    mqpacker: true,
                    import: {
                        path: paths,
                        encoding: 'utf8'
                    },
                    autoprefixer: {browsers: ['last 4 versions']},
                    'next': {
                        'customProperties': true,
                        'calc': true
                    }
                };

                if ('undefined' !== typeof taskConfig.options) {
                    if ('undefined' !== typeof taskConfig.options.pleeease) {
                        _.assign(pleeeaseOptions, taskConfig.options.pleeease);
                    }
                }

                return gulp.src(taskFiles)
                // .pipe($.sourcemaps.init())
                    .pipe($.plumber())
                    .pipe($.concat(filename))
                    .pipe($.pleeease(pleeeaseOptions))
                    .pipe($.replace('frontend/bower_components', '../assets'))
                    .pipe(gulp.dest(destination))
                    .pipe($.pleeease({
                        minifier: true
                    }))
                    .pipe($.rename({extname: '.min.css'}))
                    // .pipe($.sourcemaps.write())
                    .pipe(gulp.dest(destination));
            });
        }

        // asset tasks
        if (files.hasOwnProperty('assets')) {
            registerTasks('assets', files.assets, function (assetIdentifier, taskFiles, taskConfig) {
                var destination = distPath + '/' + assetIdentifier;
                if ('undefined' !== typeof taskConfig.dest) {
                    destination = distPath + '/' + taskConfig.dest;
                }

                return gulp.src(taskFiles)
                    .pipe(gulp.dest(destination));
            });
        }
    }

    // register watchers for all registered files
    gulp.task(taskName('watch'), function () {
        watchProfiles.forEach(function (watchProfile) {
            gulp.watch(watchProfile.files, [watchProfile.task]);
        });
    });

    // run all defined type tasks
    gulp.task(taskName('build'), [], function () {
        gulp.start(profileTasks);
    });

    // run clean + build
    gulp.task(taskName('build-clean'), [taskName('clean')], function () {
        gulp.start(taskName('build'));
    });
});

// global tasks (all profiles)
function runGlobalTasks(command) {
    Object.keys(config).forEach(function (profile) {
        gulp.start(profile + ':' + command);
    });
}

// register watchers for all registered files
gulp.task('watch', [], function () {
    runGlobalTasks('watch');
});

// run clean on all profiles
gulp.task('clean', [], function () {
    runGlobalTasks('clean');
});

// run all defined type tasks
gulp.task('build', [], function () {
    runGlobalTasks('build');
});

// run clean + build
gulp.task('build-clean', [], function () {
    runGlobalTasks('build-clean');
});

// default task runs build without clean
gulp.task('default', [], function () {
    gulp.start('build');
});
