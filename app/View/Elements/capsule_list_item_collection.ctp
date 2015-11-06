<?php
$isSortedByRating = false;
$isSortedByDiscoveries = false;
$isSortedByFavs = false;
if (isset($this->params->query['sort'])) {
    $sortKey = $this->params->query['sort'];
    if ($sortKey == \Capsules\Http\RequestContract::CAPSULE_SORT_KEY_RATING_DESC) {
        $isSortedByRating = true;
    } else if ($sortKey == \Capsules\Http\RequestContract::CAPSULE_SORT_KEY_DISCOVERY_COUNT_DESC) {
        $isSortedByDiscoveries = true;
    } else if ($sortKey == \Capsules\Http\RequestContract::CAPSULE_SORT_KEY_FAVORITE_COUNT_DESC) {
        $isSortedByFavs = true;
    }
}
?>

<?php if (isset($capsules) && is_array($capsules) && !empty($capsules)) : ?>
    <?php foreach ($capsules as $capsule) : ?>
        <?= $this->element('capsule_list_item', array(
            'capsule' => $capsule, 'isSortedByRating' => $isSortedByRating,
            'isSortedByDiscoveries' => $isSortedByDiscoveries, 'isSortedByFavs' => $isSortedByFavs
        )); ?>
    <?php endforeach; ?>
<?php endif;
