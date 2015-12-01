<h3><?php echo __("My Discoveries"); ?></h3>
<?php

$containerId = "capsule-list-view";

echo $this->element('capsule_searcher', array(
    'containerId' => $containerId,
    'baseUri' => "/api/discoveries",
    'sorts' => array(
        \Capsules\Http\RequestContract::CAPSULE_SORT_KEY_NAME_ASC => __("A - Z"),
        \Capsules\Http\RequestContract::CAPSULE_SORT_KEY_RATING_DESC => __("Best Rating"),
        \Capsules\Http\RequestContract::CAPSULE_SORT_KEY_DISCOVERY_COUNT_DESC => __("Most Discoveries"),
        \Capsules\Http\RequestContract::CAPSULE_SORT_KEY_FAVORITE_COUNT_DESC => __("Most Favorites")
    ),
    'filters' => array(
        \Capsules\Http\RequestContract::CAPSULE_FILTER_KEY_UNOPENED => array(
            'text' => __("Unopened"),
            'iconClass' => "glyphicon glyphicon-eye-open"
        ),
        \Capsules\Http\RequestContract::CAPSULE_FILTER_KEY_FAVORITES => array(
            'text' => __("Favorited"),
            'iconClass' => "glyphicon glyphicon-star"
        ),
        \Capsules\Http\RequestContract::CAPSULE_FILTER_KEY_UP_VOTES => array(
            'text' => __("Up Votes"),
            'iconClass' => "glyphicon glyphicon-chevron-up"
        ),
        \Capsules\Http\RequestContract::CAPSULE_FILTER_KEY_DOWN_VOTES => array(
            'text' => __("Down Votes"),
            'iconClass' => "glyphicon glyphicon-chevron-down"
        ),
        \Capsules\Http\RequestContract::CAPSULE_FILTER_KEY_NO_VOTES => array(
            'text' => __("No Votes"),
            'iconClass' => "glyphicon glyphicon-minus"
        )
    )
));

echo $this->element('capsule_list', array('capsules' => $capsules, 'containerId' => $containerId));
?>