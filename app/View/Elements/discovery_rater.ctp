<?php
// Make sure variables are set
if (!isset($id)) {
    $id = 0;
}
if (!isset($favorite)) {
    $favorite = 0;
}
if (!isset($rating)) {
    $rating = 0;
}

// Set the initial states
$favorite = $favorite ? 1 : 0;
if ($rating == 1 || $rating == "1") {
    $rating = 1;
} else {
    $rating = $rating == -1 || $rating == "-1" ? -1 : 0;
}

// Determine the initial states of the inputs
$isFavorite = $favorite > 0;
$isPositive = $rating == 1;
$isNegative = $rating == -1;
?>

<?php if ($id) : ?>
    <script type="text/javascript" src="/js/DiscoveryRater.js"></script>
    <script type="text/javascript">
        // Determine the object "namespace"
        var discoveryRater = {};

        // Get selectors for the elements
        discoveryRater.favoriteButton = $('.discovery-favorite-btn');
        discoveryRater.rateButton = $('.discovery-rater-btn');
        discoveryRater.rateUpButton = $('.discovery-rater-up-btn');
        discoveryRater.rateDownButton = $('.discovery-rater-down-btn');

        // Method to set the state of the favorite button
        discoveryRater.setFavoriteButtonState = function (favoriteState) {
            discoveryRater.favoriteButton.toggleClass("btn-warning", favoriteState == 1);
        };
        // Method to set the state of the rating button
        discoveryRater.setRatingButtonState = function (ratingState) {
            if (ratingState == 1) {
                discoveryRater.rateDownButton.removeClass("btn-danger");
                discoveryRater.rateUpButton.addClass("btn-success");
            } else if (ratingState == -1) {
                discoveryRater.rateDownButton.addClass("btn-danger");
                discoveryRater.rateUpButton.removeClass("btn-success");
            } else {
                discoveryRater.rateDownButton.removeClass("btn-danger");
                discoveryRater.rateUpButton.removeClass("btn-success");
            }
        };
        // Method to enable or disable the state of the buttons
        discoveryRater.setDisabledState = function (isDisabled) {
            discoveryRater.rateButton.prop('disabled', isDisabled);
            discoveryRater.favoriteButton.prop('disabled', isDisabled);
        };

        // Initialize the DiscoveryRater object
        discoveryRater.rater = new DiscoveryRater(<?= $id; ?>, <?= $favorite; ?>, <?= $rating; ?>);

        // Callback that executes before the request is made to the server to update the Discovery
        discoveryRater.rater.beforeSendCallback = function () {
            // Disable the buttons
            discoveryRater.setDisabledState(true);
        };
        // Callback that executes after the request is made to the server to update the Discovery
        discoveryRater.rater.completeCallback = function () {
            // Enable the buttons
            discoveryRater.setDisabledState(false);
        };
        // Callback that executes on a successful request to the server to update a Discovery
        discoveryRater.rater.successCallback = function (favoriteState, ratingState) {
            // Update the state of the buttons
            discoveryRater.setFavoriteButtonState(favoriteState);
            discoveryRater.setRatingButtonState(ratingState);
        };
        // Callback that executes when an error is returned from the server when trying to update a Discovery
        discoveryRater.rater.errorCallback = function (favoriteState, ratingState) {
            // Update the state of the buttons
            discoveryRater.setFavoriteButtonState(favoriteState);
            discoveryRater.setRatingButtonState(ratingState);
        };

        $(document).ready(function () {
            // Click listener for the rating button
            discoveryRater.rateButton.on('click', function () {
                var ratingState = $(this).data('rating');
                discoveryRater.rater.selectRatingState(ratingState);
            });
            // Click listener for the favorite button
            discoveryRater.favoriteButton.on('click', function () {
                var favoriteState = $(this).hasClass("btn-warning");
                discoveryRater.rater.selectFavoriteState(!favoriteState);
            });
        });
    </script>

    <div class="row">
        <div class="col-md-2 col-md-offset-5">
            <button type="button"
                    class="discovery-favorite-btn btn btn-default<?= ($isFavorite) ? " btn-warning" : ""; ?>"
                    data-id="<?= $id; ?>">
                <span class="glyphicon glyphicon-star"></span>
            </button>
        </div>
        <div class="col-md-5">
            <div class="btn-group" role="group">
                <button type="button"
                        class="discovery-rater-btn discovery-rater-up-btn btn btn-default<?= ($isPositive) ?
                            " btn-success" : ""; ?>"
                        data-id="<?= $id; ?>"
                        data-rating="1">
                    <span class="glyphicon glyphicon-chevron-up"></span>
                </button>
                <button type="button"
                        class="discovery-rater-btn discovery-rater-down-btn btn btn-default<?= ($isNegative) ?
                            " btn-danger" : ""; ?>"
                        data-id="<?= $id; ?>"
                        data-rating="-1">
                    <span class="glyphicon glyphicon-chevron-down"></span>
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>
