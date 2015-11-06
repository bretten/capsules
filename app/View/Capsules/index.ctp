<h3><?php echo __("My Capsules"); ?></h3>
<?php
$containerId = "capsule-list-view";

echo $this->element('capsule_searcher', array(
    'containerId' => $containerId,
    'baseUri' => "/api/capsules",
    'sorts' => array(
        \Capsules\Http\RequestContract::CAPSULE_SORT_KEY_NAME_ASC => __("A -Z"),
        \Capsules\Http\RequestContract::CAPSULE_SORT_KEY_RATING_DESC => __("Best Rating"),
        \Capsules\Http\RequestContract::CAPSULE_SORT_KEY_DISCOVERY_COUNT_DESC => __("Most Discoveries"),
        \Capsules\Http\RequestContract::CAPSULE_SORT_KEY_FAVORITE_COUNT_DESC => __("Most Favorites")
    )
));

echo $this->element('capsule_list', array('capsules' => $capsules, 'containerId' => $containerId));
?>
