const { src, dest } = require('gulp')
const concat = require("gulp-concat")
const iife = require("gulp-iife")
const babel = require("gulp-babel")
const uglify = require("gulp-uglify")
const insert = require("gulp-insert")


function addlhtml() {
    return src('additionalhtml/*.js')
        .pipe(concat('footer.js'))
        .pipe(iife())
        .pipe(babel({ presets: ['@babel/preset-env'] }))
        .pipe(uglify())
        .pipe(insert.prepend(`<script>\n// minified ${Date()} - see https://github.com/cca/moodle_cli\n`))
        .pipe(insert.append('\n</script>'))
        .pipe(dest('dist'))
}

module.exports = {
    'addlhtml': addlhtml,
    default: addlhtml
}
