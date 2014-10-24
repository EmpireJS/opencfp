var gulp = require('gulp');
var sass = require('gulp-ruby-sass');
var autoprefixer = require('gulp-autoprefixer');
var minifycss = require('gulp-minify-css');
var rename = require('gulp-rename');

gulp.task('styles', function() {
  return gulp.src('web/assets/scss/site.scss')
    .pipe(sass({ style: 'expanded' }))
    .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1'))
    .pipe(gulp.dest('web/assets/css'))
    .pipe(rename({suffix: '.min'}))
    .pipe(minifycss())
    .pipe(gulp.dest('web/assets/css'));
});

gulp.task('watch', function() {
  gulp.watch('web/assets/scss/**/*.scss', ['styles']);
});

gulp.task('default', ['styles', 'watch'], function() {

});