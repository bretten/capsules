<?php
// Set the sort flags
if (!isset($isSortedByRating)) {
    $isSortedByRating = false;
}
if (!isset($isSortedByDiscoveries)) {
    $isSortedByDiscoveries = false;
}
if (!isset($isSortedByFavs)) {
    $isSortedByFavs = false;
}
?>

<a href="#" class="list-group-item capsule-list-item" data-id="<?= $capsule['Capsule']['id']; ?>">
    <div class="row">
        <div class="col-md-1">
            <?php if (isset($capsule['Memoir'])) : ?>
                <img src="/api/memoir/<?= $capsule['Memoir']['id']; ?>" alt="<?= $capsule['Memoir']['title']; ?>"
                     class="img-thumbnail">
            <?php endif; ?>
        </div>

        <div class="col-md-9">
            <h4 class="text-format-overflow">
                <?php if (isset($capsule['Discovery'])) : ?>
                    <?php if ($capsule['Discovery']['favorite']) : ?>
                        <span class="glyphicon glyphicon-star glyphicon-warning"></span>
                    <?php else : ?>
                        <span class="glyphicon glyphicon-star glyphicon-neutral"></span>
                    <?php endif; ?>
                    <?php if ($capsule['Discovery']['rating'] == 1) : ?>
                        <span class="glyphicon glyphicon-chevron-up glyphicon-positive"></span>
                    <?php elseif ($capsule['Discovery']['rating'] == -1) : ?>
                        <span class="glyphicon glyphicon-chevron-down glyphicon-negative"></span>
                    <?php else : ?>
                        <span class="glyphicon glyphicon-minus glyphicon-neutral"></span>
                    <?php endif; ?>
                <?php endif; ?>
                <strong><?= h($capsule['Capsule']['name']); ?></strong>
            </h4>

            <h5 class="list-group-item-text">
                <?php if (isset($capsule['Discovery'])) : ?>
                    <small>
                        <em>
                            <?= __("Discovered on") . " " . date('F j, Y, g:i a',
                                strtotime($capsule['Discovery']['created'])); ?>
                        </em>
                    </small>
                <?php else : ?>
                    <small>
                        <em>
                            <?= __("Buried on") . " " . date('F j, Y, g:i a',
                                strtotime($capsule['Capsule']['created'])); ?>
                        </em>
                    </small>
                <?php endif; ?>
            </h5>
        </div>

        <div class="col-md-2 pull-right">
            <div class="text-right">
                <span class="badge<?= $isSortedByRating ? " alert-success" : " alert-info"; ?>">
                    <span class="glyphicon glyphicon-fire"></span><?= $capsule['Capsule']['total_rating']; ?>
                </span>
                <span class="badge<?= $isSortedByDiscoveries ? " alert-success" : " alert-info"; ?>">
                    <span
                        class="glyphicon glyphicon-map-marker"></span><?= $capsule['Capsule']['discovery_count']; ?>
                </span>
                <span class="badge<?= $isSortedByFavs ? " alert-success" : " alert-info"; ?>">
                    <span class="glyphicon glyphicon-star"></span><?= $capsule['Capsule']['favorite_count']; ?>
                </span>
            </div>
        </div>
    </div>
</a>
