function loadSection(select, page) {
    window.location = "./" + page + "?term=" + select.value;
}

function onAjaxContentLoad() {
    onDOMChange();
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
    
    element.innerHTML = AJAX_CONTENT_LOADING;
    
    ajaxContentIntoContainer({ 
        url: contentURL+"&ajax=1", // the url to get the page content from 
        container: element, // the container to dump the content into 
        timeout: 30, // how long to wait for the server before returning an error 
        success: function() {
            onAjaxContentLoad();
        },
        error: function(e) {
            element.innerHTML = AJAX_CONTENT_LOAD_FAILED;
            onAjaxContentLoad();
        }
    });
}

function loadTab(tabId, contentURL) {
    //console.log('loading ' + tab + ' from ' + contentURL);
    var element = document.getElementById(tabId+'-tabbody');
    
    if (!hasClass(element, 'loaded')) {
        element.innerHTML = AJAX_CONTENT_LOADING;
        
        ajaxContentIntoContainer({ 
            url: contentURL, // the url to get the page content from 
            container: element, // the container to dump the content into 
            timeout: 30, // how long to wait for the server before returning an error 
            success: function() {
                addClass(element, 'loaded');
                onAjaxContentLoad();
            },
            error: function(e) {
                element.innerHTML = AJAX_CONTENT_LOAD_FAILED;
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
    
    element.innerHTML = AJAX_CONTENT_LOADING;

    ajaxContentIntoContainer({ 
        url: contentURL + '&ajax=1&ajaxgroup=1', // the url to get the page content from 
        container: element, // the container to dump the content into 
        timeout: 30, // how long to wait for the server before returning an error 
        success: function() {
            onAjaxContentLoad();
        },
        error: function(e) {
            element.innerHTML = AJAX_CONTENT_LOAD_FAILED;
            onAjaxContentLoad();
        }
    });
    
    return false;
}
