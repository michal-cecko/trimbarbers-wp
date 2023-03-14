'use strict';
let gulp = require('gulp'),
    sass = require('gulp-dart-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    autoprefixer = require('gulp-autoprefixer'),
    cleanCSS = require('gulp-clean-css'),
    rename = require('gulp-rename'),
    uglify = require('gulp-uglify'),
    ts = require("gulp-typescript"),
    tsProject = ts.createProject("tsconfig.json");

sass.compiler = require('sass')

gulp.task('minify', function () {
    return gulp.src('dist/css/main.css')
        .pipe(cleanCSS())
        .pipe(rename('main.min.css'))
        .pipe(gulp.dest('dist/css/'))
});

gulp.task('scripts', function () {
    return gulp.src(['assets/js/**/*.js', '!assets/js/**/*.min.js'])
        .pipe(gulp.dest('dist/js/'))
});

gulp.task('sass', function () {
    return gulp.src('assets/sass/main.scss')
        .pipe(sass({outputStyle: 'compressed'}))
        .pipe(sourcemaps.init())
        .pipe(autoprefixer())
        .pipe(cleanCSS())
        .pipe(sourcemaps.write())
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('dist/css/'))
});

gulp.task('admin-sass', function () {
    return gulp.src('assets/sass/admin/**/*.scss')
        .pipe(sass({outputStyle: 'compressed'}))
        .pipe(sourcemaps.init())
        .pipe(autoprefixer())
        .pipe(cleanCSS())
        .pipe(sourcemaps.write())
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('dist/css/admin'))
});

gulp.task('assets:watch', function () {
    gulp.watch(['assets/sass/**/*.scss', '!assets/sass/admin/**/*.scss'], gulp.series('sass'))
    gulp.watch(['assets/sass/admin/**/*.scss'], gulp.series('admin-sass'))
    gulp.watch('assets/js/**/*.js', gulp.series('scripts'))
});

gulp.task('production', gulp.series('sass', 'admin-sass', 'minify', 'scripts'))

