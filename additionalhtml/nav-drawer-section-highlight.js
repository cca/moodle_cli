// run on course home pages
if (location.pathname.match('/course/view.php')) {
    let main = ($) => {
        // is there a highlighted section? find its name
        let $highlightedSection = $('.topics li.section.current')

        if ($highlightedSection.length === 1) {
            name = $highlightedSection.attr('aria-label').trim()

            $('#nav-drawer li').filter((idx, el) => {
                return $(el).find('.media-body').text().trim() === name ? true : false
            }).find('a').addClass('active').find('.media-body').addClass('font-weight-bold')
        }
    }

    // wait for jquery to be available, it sucks that both jQuery and require
    // come after the footer HTML so we have to do this hack
    let interval = setInterval(()=>{
        if (jQuery) {
            clearInterval(interval)
            main(jQuery)
        }
    }, 400)
}
