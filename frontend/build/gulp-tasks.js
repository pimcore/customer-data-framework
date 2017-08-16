'use strict';

var _ = require('lodash'),
    path = require('path');

module.exports = function (gulp, plugins, config) {
    /*
     Registers a clean, watch, build and build-clean task for every profile (e.g. frontend). Exposes every type (e.g. scripts
     and every result file (e.g. scripts:script.js) as extra task which is called from aggregating tasks. Example structure:

     ├── frontend:clean
     ├── frontend:scripts:lib.js
     ├── frontend:scripts:script.js
     ├─┬ frontend:scripts
     │ ├── frontend:scripts:lib.js
     │ └── frontend:scripts:script.js
     ├── frontend:styles:lib.css
     ├── frontend:styles:style.css
     ├─┬ frontend:styles
     │ ├── frontend:styles:lib.css
     │ └── frontend:styles:style.css
     ├── frontend:assets:fonts
     ├── frontend:assets:iCheck
     ├─┬ frontend:assets
     │ ├── frontend:assets:fonts
     │ └── frontend:assets:iCheck
     ├── frontend:watch
     ├── frontend:build
     ├─┬ frontend:build-clean
     │ └── frontend:clean
     ├── watch
     ├── clean
     ├── build
     ├── build-clean
     └── default
     */

    var $ = plugins;

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
                        .pipe($.replace('node_modules', '../assets'))
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
};
