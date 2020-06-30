// run on course home pages
if (location.pathname.match('/course/view.php')) {
    let d = document
    // we don't have jQuery yet so use vanilla JS
    // is there a highlighted section? find its name
    let highlightedSection = d.querySelector('.topics li.section.current')

    if (highlightedSection) {
        let name = highlightedSection.ariaLabel.trim()

        // REM: QSA => NodeList & not Array, but forEach is widely supported now
        d.querySelectorAll('#nav-drawer li').forEach(item => {
            if (item.querySelector('.media-body').textContent.trim() === name) {
                item.querySelector('a').classList.add('active')
                item.querySelector('.media-body').classList.add('font-weight-bold')
            }
        })
    }
}
