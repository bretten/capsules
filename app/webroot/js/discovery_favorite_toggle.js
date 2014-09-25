var discoveryFavoriteToggle = {};

$(document).ready(function() {
    $('.discovery-favorite-toggle-container').on('click', '.discovery-favorite-toggle-btn', function(e) {
        var ele = $(this);
        var id = $(this).parents('.discovery-favorite-toggle-container').attr('data-id');
        if (id > 0) {
            // Send the request to the server
            $.ajax({
                type: 'POST',
                url: '/discoveries/favorite/',
                data: {'data[id]': id},
                dataType: 'json',
                success: function(data, textStatus, jqXHR) {
                    if (data.hasOwnProperty('favorite')) {
                        discoveryFavoriteToggle.onSuccess(ele, data.favorite);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    return false;
                }
            });
        }

    });
});

discoveryFavoriteToggle.onSuccess = function(ele, favorite) {
    if (favorite) {
        ele.addClass('btn-warning');
    } else {
        ele.removeClass('btn-warning');
    }
}