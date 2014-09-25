var discoveryRater = {};

$(document).ready(function() {
    $('.discovery-rater-container').on('click', '.discovery-rater-btn', function(e) {
        var container = $(this).parents('.discovery-rater-container');
        var id = container.attr('data-id');
        var rating;
        if (id > 0 && ($(this).data('rating') == 1 || $(this).data('rating') == -1)) {
            rating = $(this).data('rating');
            // Send the request to the server
            $.ajax({
                type: 'POST',
                url: '/discoveries/rate/',
                data: {'data[id]': id, 'data[rating]': rating},
                dataType: 'json',
                success: function(data, textStatus, jqXHR) {
                    if (data.hasOwnProperty('rating')) {
                        discoveryRater.onSuccess(container, data.rating);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    return false;
                }
            });
        }

    });
});

discoveryRater.onSuccess = function(container, rating) {
    container.find('.discovery-rater-btn').removeClass('btn-success').removeClass('btn-danger');
    if (rating > 0) {
        container.children().first().find('.discovery-rater-btn').addClass('btn-success');
    } else if (rating < 0) {
        container.children().last().find('.discovery-rater-btn').addClass('btn-danger');
    }
}