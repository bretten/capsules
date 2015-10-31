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
     * The ways Capsules can be sorted
     *
     * @var array
     */
    public static $CAPSULE_SORT_MAP = array(
        0 => 'Capsule.name ASC',
        1 => 'Capsule.name DESC',
        2 => 'Capsule.discovery_count DESC'
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
            return RequestContract::$CAPSULE_SORT_MAP[0];
        }
    }

}
