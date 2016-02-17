function unirgyDomLoaded(func)
{
    if ((/msie [1-7]\./i).test(navigator.userAgent)) {
        Event.observe(window, 'load', func)
    } else {
        document.observe("dom:loaded", func)
    }
}
