function getUrlParameter(sParam)
{
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) 
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam) 
        {
            return sParameterName[1];
        }
    }
}

function nl2br(str, is_xhtml)
{
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>'; // Adjust comment to avoid issue on phpjs.org display

    return (str + '')
    .replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}



function getUrlParameters()
{
    var params = {};
    var search = window.location.search.substring(1);
    if (!search) {
        return params;
    }

    search.split('&').forEach(function (part) {
        if (!part) {
            return;
        }
        var pair = part.split('=');
        var key = decodeURIComponent(pair[0] || '');
        if (!key) {
            return;
        }
        params[key] = decodeURIComponent(pair[1] || '');
    });

    return params;
}

function buildPortalUrl(params, hash)
{
    var query = Object.keys(params || {}).filter(function (key) {
        return params[key] !== undefined && params[key] !== null && params[key] !== '';
    }).map(function (key) {
        return encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
    }).join('&');

    var url = query ? '?' + query : window.location.pathname;
    if (hash) {
        return url + '#' + hash.replace(/^#/, '');
    }

    return url;
}
