# CCA Moodle `additionalhtml` settings

Formerly its own repo at https://github.com/cca/moodle_additionalhtml

We use the three `additionalhtml` settings (`head`, `topofbody`, `footer`) to add customizations to our Moodle theme. These are powerful settings because they can be applied to every page on the site, giving us the ability to apply fixes across the whole app without touching a bunch of plugins or settings.

`additionalhtmlhead` ~is the place for CSS tweaks~ add CSS edits under the theme, e.g.  Site administration > Appearance > Themes > Boost > Advanced Settings > `Raw SCSS`.

`additionalhtmlfooter` is the spot for JavaScript.

I suspect we will not find a use for `topofbody` but it's possible that that settings could be used for warnings or announcements.

Moodle documentation for these settings resides here: https://docs.moodle.org/32/en/Header_and_footer

# LICENSE

[ECL Version 2.0](https://opensource.org/licenses/ECL-2.0)
