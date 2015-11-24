/**
 * Object that handles toggling the favorite status or rating a Discovery
 *
 * @param id The ID of the Discovery
 * @param favoriteState The initial favorite state
 * @param ratingState The initial rating state
 * @constructor
 * @author https://github.com/bretten
 */
var DiscoveryRater = function (id, favoriteState, ratingState) {
    this.id = id;
    this.serverFavoriteState = this.selectedFavoriteState = favoriteState;
    this.serverRatingState = this.selectedRatingState = ratingState;
};

/**
 * The ID of the Discovery
 *
 * @type {number}
 */
DiscoveryRater.prototype.id = 0;

/**
 * The favorite state value from the server
 *
 * @type {number}
 */
DiscoveryRater.prototype.serverFavoriteState = 0;

/**
 * The rating state value from the server
 *
 * @type {number}
 */
DiscoveryRater.prototype.serverRatingState = 0;

/**
 * The favorite state that was selected by the user
 *
 * @type {number}
 */
DiscoveryRater.prototype.selectedFavoriteState = 0;

/**
 * The rating state that was selected by the user
 *
 * @type {number}
 */
DiscoveryRater.prototype.selectedRatingState = 0;

/**
 * Flag to determine if a HTTP request is currently being made to the server
 *
 * @type {boolean}
 */
DiscoveryRater.prototype.isRequestInProgress = false;

/**
 * Callback method that executes before a request is made to the server
 *
 * @type {function|null}
 */
DiscoveryRater.prototype.beforeSendCallback = null;

/**
 * Callback method that executes after a request is made to the server
 *
 * @type {function|null}
 */
DiscoveryRater.prototype.completeCallback = null;

/**
 * Callback method that executes after a successful request is made to the server
 *
 * @type {function|null}
 */
DiscoveryRater.prototype.successCallback = null;

/**
 * Callback method that executes after an error is returned from the server
 *
 * @type {function|null}
 */
DiscoveryRater.prototype.errorCallback = null;

/**
 * Sets the selected favorite state
 *
 * @param favoriteState The favorite state
 */
DiscoveryRater.prototype.selectFavoriteState = function (favoriteState) {
    this.selectedFavoriteState = favoriteState == true || favoriteState == 1 || favoriteState == "1" ? 1 : 0;
    this.sendRequest();
};

/**
 * Sets the selected rating state
 *
 * @param ratingState The rating state
 */
DiscoveryRater.prototype.selectRatingState = function (ratingState) {
    if ((ratingState == "1" || ratingState == 1) && this.serverRatingState != 1) {
        this.selectedRatingState = 1;
    } else if ((ratingState == "-1" || ratingState == -1) && this.serverRatingState != -1) {
        this.selectedRatingState = -1;
    } else {
        this.selectedRatingState = 0;
    }
    this.sendRequest();
};

/**
 * Sends a request to the server to update the Discovery using the selected favorite and rating states
 */
DiscoveryRater.prototype.sendRequest = function () {
    if (this.isRequestInProgress) {
        return;
    }

    $.ajax({
        type: 'POST',
        url: '/api/discovery/' + this.id,
        data: {'data[favorite]': this.selectedFavoriteState, 'data[rating]': this.selectedRatingState},
        dataType: 'json',
        beforeSend: function (jqXHR, settings) {
            // Indicate that a request is in progress
            this.isRequestInProgress = true;
            // Execute the callback
            if ($.isFunction(this.beforeSendCallback)) {
                this.beforeSendCallback(jqXHR, settings);
            }
        }.bind(this),
        complete: function (jqXHR, textStatus) {
            // Indicate that the request has finished
            this.isRequestInProgress = false;
            // Execute the callback
            if ($.isFunction(this.completeCallback)) {
                this.completeCallback(jqXHR, textStatus);
            }
        }.bind(this),
        success: function (data, textStatus, jqXHR) {
            // The selected states now reflect the server-side state
            this.serverFavoriteState = this.selectedFavoriteState;
            this.serverRatingState = this.selectedRatingState;
            // Execute the callback
            if ($.isFunction(this.successCallback)) {
                this.successCallback(this.serverFavoriteState, this.serverRatingState, data, textStatus, jqXHR);
            }
        }.bind(this),
        error: function (jqXHR, textStatus, errorThrown) {
            // The request was unsuccessful, so revert the selected states back to the server-side state
            this.selectedFavoriteState = this.serverFavoriteState;
            this.selectedRatingState = this.serverRatingState;
            // Execute the callback
            if ($.isFunction(this.errorCallback)) {
                this.errorCallback(this.serverFavoriteState, this.serverRatingState, jqXHR, textStatus, errorThrown);
            }
        }.bind(this)
    });
};
