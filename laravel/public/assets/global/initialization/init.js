$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

/**
 * jQuery AJAX
 * @param url
 * @param data
 * @param callback
 * @param type
 */
function xAjax(url, data, callback, type) {
    if (type === undefined) type = 'post';
    $.ajax({
        'url': url,
        'type': type,
        'data': data,
        'dataType': 'json',
        'success': function (data, textStatus) {
            if (textStatus === 'success') {
                callback({
                    'code': 0,
                    'message': textStatus,
                    'data': data
                });
            } else {
                callback({
                    'code': 500,
                    'message': textStatus,
                    'data': null
                });
            }

        },
        'error': function (XMLHttpRequest, textStatus) {
            callback({
                'code': 500,
                'message': textStatus,
                'data': XMLHttpRequest
            });
        }
    });
}

function xPost(url, data, callback) {
    xAjax(url, data, callback, 'post')
}