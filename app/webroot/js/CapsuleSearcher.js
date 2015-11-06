/**
 * Represents a searching UI object that searches for Capsules and provides functionality to page, filter, and
 * sort results.
 *
 * @constructor
 * @author https://github.com/bretten
 */
var CapsuleSearcher = function () {

};

/**
 * The base URI that all HTTP requests will be directed at
 *
 * @type {string}
 */
CapsuleSearcher.prototype.baseUri = "/";

/**
 * The current page of results that are being displayed
 *
 * @type {number}
 */
CapsuleSearcher.prototype.page = 1;

/**
 * The current sort key
 *
 * @type {number|string|null}
 */
CapsuleSearcher.prototype.sortKey = null;

/**
 * The current filter key
 *
 * @type {number|string|null}
 */
CapsuleSearcher.prototype.filterKey = null;

/**
 * The current search string
 *
 * @type {string|null}
 */
CapsuleSearcher.prototype.searchString = null;

/**
 * Callback that will be fired before the HTTP request to update the results is sent
 *
 * @type {function|null}
 */
CapsuleSearcher.prototype.beforeSendCallback = null;

/**
 * Callback that will be fired after the HTTP request to update the results is sent
 *
 * @type {function|null}
 */
CapsuleSearcher.prototype.completeCallback = null;

/**
 * Callback that will be fired upon success of the HTTP request to update the results
 *
 * @type {function|null}
 */
CapsuleSearcher.prototype.successCallback = null;

/**
 * Callback that will be fired upon failure of the HTTP request to update the results
 *
 * @type {function|null}
 */
CapsuleSearcher.prototype.errorCallback = null;

/**
 * Sets the base URI
 *
 * @param baseUri The base URI
 * @returns {CapsuleSearcher} Reference to the current instance for chaining
 */
CapsuleSearcher.prototype.setBaseUri = function (baseUri) {
    this.baseUri = baseUri;
    return this;
};

/**
 * Sets the page
 *
 * @param {number|string|null} page The page
 * @returns {CapsuleSearcher} Reference to the current instance for chaining
 */
CapsuleSearcher.prototype.setPage = function (page) {
    this.page = page;
    return this;
};

/**
 * Sets the sort key
 *
 * @param {number|string|null} sortKey The sort key
 * @returns {CapsuleSearcher} Reference to the current instance for chaining
 */
CapsuleSearcher.prototype.setSortKey = function (sortKey) {
    this.sortKey = sortKey;
    return this;
};

/**
 * Sets the filter key
 *
 * @param {number|string|null} filterKey The filter key
 * @returns {CapsuleSearcher} Reference to the current instance for chaining
 */
CapsuleSearcher.prototype.setFilterKey = function (filterKey) {
    this.filterKey = filterKey;
    return this;
};

/**
 * Sets the search string
 *
 * @param {string|null} searchString The search string
 * @returns {CapsuleSearcher} Reference to the current instance for chaining
 */
CapsuleSearcher.prototype.setSearchString = function (searchString) {
    this.searchString = searchString;
    return this;
};

/**
 * Sets the beforeSend callback
 *
 * @param {function|null} beforeSendCallback The beforeSend callback
 * @returns {CapsuleSearcher} Reference to the current instance for chaining
 */
CapsuleSearcher.prototype.setBeforeSendCallback = function (beforeSendCallback) {
    this.beforeSendCallback = beforeSendCallback;
    return this;
};

/**
 * Sets the complete callback
 *
 * @param {function|null} completeCallback
 * @returns {CapsuleSearcher} Reference to the current instance for chaining
 */
CapsuleSearcher.prototype.setCompleteCallback = function (completeCallback) {
    this.completeCallback = completeCallback;
    return this;
};

/**
 * Sets the success callback
 *
 * @param {function|null} successCallback
 * @returns {CapsuleSearcher} Reference to the current instance for chaining
 */
CapsuleSearcher.prototype.setSuccessCallback = function (successCallback) {
    this.successCallback = successCallback;
    return this;
};

/**
 * Sets the error callback
 *
 * @param {function|null} errorCallback
 * @returns {CapsuleSearcher} Reference to the current instance for chaining
 */
CapsuleSearcher.prototype.setErrorCallback = function (errorCallback) {
    this.errorCallback = errorCallback;
    return this;
};

/**
 * Builds the query string based on the current page, sort key, filter key and search string
 *
 * @returns {string} A query string containing query parameters for page, sort key, filter key, and search string
 */
CapsuleSearcher.prototype.buildQueryString = function () {
    var queryString = "?page=" + this.page;
    if (this.sortKey !== null && this.sortKey !== 'undefined' && this.sortKey.trim()) {
        queryString += "&sort=" + this.sortKey;
    }
    if (this.filterKey !== null && this.filterKey !== 'undefined' && this.filterKey.trim()) {
        queryString += "&filter=" + this.filterKey;
    }
    if (this.searchString !== null && this.searchString !== 'undefined' && this.searchString.trim()) {
        queryString += "&search=" + this.searchString;
    }
    return queryString;
};

/**
 * Increments the current page
 */
CapsuleSearcher.prototype.incrementPage = function () {
    this.page++;
};

/**
 * Decrements the current page. Only decrements when the page is greater than 1 to prevent going to a negative page
 */
CapsuleSearcher.prototype.decrementPage = function () {
    if (this.page > 1) {
        this.page--;
    }
};

/**
 * Resets the page number to the beginning
 */
CapsuleSearcher.prototype.resetPageNumber = function () {
    this.page = 1;
};

/**
 * Resets all query parameters
 */
CapsuleSearcher.prototype.resetQueryParams = function () {
    this.resetPageNumber();
    this.sortKey = null;
    this.filterKey = null;
    this.searchString = null;
};

/**
 * Sends an HTTP request using AJAX using the current query parameters and delegates handling to the callbacks
 */
CapsuleSearcher.prototype.getResults = function () {
    $.ajax({
        type: 'GET',
        url: this.baseUri + this.buildQueryString(),
        beforeSend: (function (jqXHR, settings) {
            if ($.isFunction(this.beforeSendCallback)) {
                this.beforeSendCallback(jqXHR, settings);
            }
        }).bind(this),
        complete: (function (jqXHR, textStatus) {
            if ($.isFunction(this.completeCallback)) {
                this.completeCallback(jqXHR, textStatus);
            }
        }).bind(this),
        success: (function (data, textStatus, jqXHR) {
            if ($.isFunction(this.successCallback)) {
                this.successCallback(data, textStatus, jqXHR);
            }
        }).bind(this),
        error: (function (jqXHR, textStatus, errorThrown) {
            if ($.isFunction(this.errorCallback)) {
                this.errorCallback(jqXHR, textStatus, errorThrown);
            }
        }).bind(this)
    });
};
