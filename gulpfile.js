const gulp = require('gulp');

gulp.task('default', () => {
    gulp.src('./upload/**/*')
        .pipe(gulp.dest('../portit/'));
});

gulp.task('watch', ['default'] ,()=>{
    gulp.watch('./upload/**/*', ['default']);
});