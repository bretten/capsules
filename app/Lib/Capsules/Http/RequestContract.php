<?php
namespace Capsules\Http;

/**
 * Provides a contract of HTTP related items that a client should follow in order to properly communicate
 * with the server.
 *
 * @package Capsules\Http
 * @author Brett Namba
 */
class RequestContract {

    /**
     * Request parameter name for sorting results
     */
    const PARAM_NAME_SORT = "sort";

    /**
     * Request parameter name for filtering results
     */
    const PARAM_NAME_FILTER = "filter";

    /**
     * Request parameter name for paging results
     */
    const PARAM_NAME_PAGE = "page";

    /**
     * Request parameter name for filtering results by search keywords
     */
    const PARAM_NAME_SEARCH = "search";

    /**
     * Sort key for sorting Capsules by name, A-Z
     */
    const CAPSULE_SORT_KEY_NAME_ASC = 0;

    /**
     * Sort key for sorting Capsules from highest to lowest rating
     */
    const CAPSULE_SORT_KEY_RATING_DESC = 1;

    /**
     * Sort key for sorting Capsules from most to least discoveries
     */
    const CAPSULE_SORT_KEY_DISCOVERY_COUNT_DESC = 2;

    /**
     * Sort key for sorting Capsules from most to least favorites
     */
    const CAPSULE_SORT_KEY_FAVORITE_COUNT_DESC = 3;

    /**
     * Filter key for filtering Capsules that have been set as favorites
     */
    const CAPSULE_FILTER_KEY_FAVORITES = 0;

    /**
     * Filter key for filtering Capsules that have been rated up
     */
    const CAPSULE_FILTER_KEY_UP_VOTES = 1;

    /**
     * Filter key for filtering Capsules that have been rated down
     */
    const CAPSULE_FILTER_KEY_DOWN_VOTES = 2;

    /**
     * Filter key for filtering Capsules that have not been rated
     */
    const CAPSULE_FILTER_KEY_NO_VOTES = 3;

    /**
     * Filter key for filtering Capsules that have not been opened
     */
    const CAPSULE_FILTER_KEY_UNOPENED = 4;

    /**
     * Mapping of Capsule sort keys corresponding to ORDER clauses
     *
     * @var array
     */
    public static $CAPSULE_SORT_MAP = array(
        RequestContract::CAPSULE_SORT_KEY_NAME_ASC => 'Capsule.name ASC',
        RequestContract::CAPSULE_SORT_KEY_RATING_DESC => 'Capsule.total_rating DESC',
        RequestContract::CAPSULE_SORT_KEY_DISCOVERY_COUNT_DESC => 'Capsule.discovery_count DESC',
        RequestContract::CAPSULE_SORT_KEY_FAVORITE_COUNT_DESC => 'Capsule.favorite_count DESC'
    );

    /**
     * Mapping of Capsule filter keys to the corresponding WHERE clause parameters that will be added to the
     * database array query
     *
     * @var array
     */
    public static $CAPSULE_FILTER_MAP = array(
        RequestContract::CAPSULE_FILTER_KEY_FAVORITES => array('Discovery.favorite >=' => 1),
        RequestContract::CAPSULE_FILTER_KEY_UP_VOTES => array('Discovery.rating >=' => 1),
        RequestContract::CAPSULE_FILTER_KEY_DOWN_VOTES => array('Discovery.rating <=' => -1),
        RequestContract::CAPSULE_FILTER_KEY_NO_VOTES => array('Discovery.rating' => 0),
        RequestContract::CAPSULE_FILTER_KEY_UNOPENED => array('Discovery.opened' => false)
    );

    /**
     * Gets the corresponding Capsule SQL order string for the specified Capsule sort key
     *
     * @param mixed $sortKey The Capsule sort key
     * @return string The ORDER clause string
     */
    public static function getCapsuleOrderBySortKey($sortKey) {
        if (array_key_exists($sortKey, RequestContract::$CAPSULE_SORT_MAP)) {
            return RequestContract::$CAPSULE_SORT_MAP[$sortKey];
        } else {
            return RequestContract::$CAPSULE_SORT_MAP[RequestContract::CAPSULE_SORT_KEY_NAME_ASC];
        }
    }

    /**
     * Given the filter key, appends the corresponding WHERE condition to the specified database query array
     *
     * @param mixed $filterKey The Capsule filter key
     * @param array $query The query to append to
     * @return array The updated query if a matching filter was found, otherwise the same query
     */
    public static function appendCapsuleFilterToQuery($filterKey, array $query = array()) {
        if (array_key_exists($filterKey, RequestContract::$CAPSULE_FILTER_MAP)) {
            $append = array(
                'conditions' => RequestContract::$CAPSULE_FILTER_MAP[$filterKey]
            );
            return array_merge_recursive($query, $append);
        } else {
            return $query;
        }
    }

}
