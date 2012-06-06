function loadSection(select, page) {
    window.location = "./" + page + "?term=" + select.value;
}

function onAjaxContentLoad() {
    onDOMChange();
}

// needs to be overridden by tablet
function scrollContentToTop() {
    scrollToTop();
}

function switchPage(link, contentURL) {
    var element = link.parentNode;
    while (element) {
        if (hasClass(element, "pager-container")) {
            break;
        }
        element = element.parentNode;
    }
    if (!element) { return; }
    
    scrollContentToTop();
    
    ajaxContentIntoContainer({ 
        url: contentURL+"&ajax=1", // the url to get the page content from 
        container: element, // the container to dump the content into 
        timeout: 30, // how long to wait for the server before returning an error 
        success: function() {
            onAjaxContentLoad();
        },
        error: function(e) {
            onAjaxContentLoad();
        }
    });
}

function loadTab(tabId, contentURL) {
    //console.log('loading ' + tab + ' from ' + contentURL);
    var element = document.getElementById(tabId+'-tabbody');
    
    if (!hasClass(element, 'loaded')) {
        ajaxContentIntoContainer({ 
            url: contentURL, // the url to get the page content from 
            container: element, // the container to dump the content into 
            timeout: 30, // how long to wait for the server before returning an error 
            success: function() {
                addClass(element, 'loaded');
                onAjaxContentLoad();
            },
            error: function(e) {
                onAjaxContentLoad();
            }
        });
    }
}

function updateGroupTab(clicked, tabId, contentURL) {
    var groupList = document.getElementById(tabId + '-tabstrip');
    var element = document.getElementById(tabId + '-content');
    
    var items = groupList.getElementsByTagName('li');
    for (var i = 0; i < items.length; i++) {
        items[i].className = items[i] == clicked.parentNode ? 'active' :'';
    }
    
    ajaxContentIntoContainer({ 
        url: contentURL + '&ajax=1&ajaxgroup=1', // the url to get the page content from 
        container: element, // the container to dump the content into 
        timeout: 30, // how long to wait for the server before returning an error 
        success: function() {
            onAjaxContentLoad();
        },
        error: function(e) {
            onAjaxContentLoad();
        }
    });
    
    return false;
}
