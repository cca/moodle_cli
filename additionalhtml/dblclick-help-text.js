// https://developer.mozilla.org/en-US/docs/Mozilla/Add-ons/WebExtensions/Interact_with_the_clipboard
function copyHelpToClipboard(event) {
    let content = event.target.parentElement.dataset.content
    if (content) {
        // strip HTML tags
        content = content.replace(/(<([^>]+)>)/gi, "")
        navigator.clipboard.writeText(content).then(function() {
            console.log('text copied to clipboard')
        }, function () {
            console.error('error in copying text to clipboard')
        })
    }
}

document.querySelectorAll('.fa-question-circle.text-info').forEach(el => {
    el.addEventListener('dblclick', copyHelpToClipboard)
});
