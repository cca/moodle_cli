# CCA Moodle `additionalhtml` settings

Formerly its own repo at https://github.com/cca/moodle_additionalhtml

We use the three `additionalhtml` settings (`head`, `topofbody`, `footer`) to add customizations to our Moodle theme. These are powerful settings because they can be applied to every page on the site, giving us the ability to apply fixes across the whole app without touching a bunch of plugins or settings.

`additionalhtmlhead` ~is the place for CSS tweaks~ add CSS edits under the theme, e.g.  Site administration > Appearance > Themes > Boost > Advanced Settings > `Raw SCSS`.

`additionalhtmlfooter` is the spot for JavaScript. Note that code loaded here happens before jQuery or require.js are loaded, so we are pretty much forced to run a `setInterval` loop to wait for other resources to be available if we need them.

The npm script `npm run js` will run a gulp build process over all the .js files in this directory, copy them to your clipboard, and open the appropriate Moodle settings page for you to paste into.

I suspect we will not find a use for `topofbody` but it's possible that that settings could be used for warnings or announcements.

Moodle documentation for these settings resides here: https://docs.moodle.org/32/en/Header_and_footer

# LICENSE

[ECL Version 2.0](https://opensource.org/licenses/ECL-2.0)
