<?php
    $this->Paginator->options(array(
        'update' => '#tab-pane-capsules',
        'evalScripts' => true,
        'before' => 'mapView.paginationUri.capsules = $(this).attr("url")'
    ));
    $favSorted = isset($this->params['named']) && isset($this->params['named']['sort']) && $this->params['named']['sort'] === 'favorite_count';
    $discoverySorted = isset($this->params['named']) && isset($this->params['named']['sort']) && $this->params['named']['sort'] === 'discovery_count';
    $scoreSorted = isset($this->params['named']) && isset($this->params['named']['sort']) && $this->params['named']['sort'] === 'total_rating';
?>

<div class="capsules index">
    <?php
        echo $this->element('searcher', array(
            'container' => '#tab-pane-capsules',
            'controller' => 'capsules',
            'action' => 'index',
            'hasSearch' => true,
            'before' => 'mapView.paginationUri.capsules = searcher.buildUri();',
            'sorts' => array(
                '/sort:name/direction:asc' => 'A - Z',
                '/sort:name/direction:desc' => 'Z - A',
                '/sort:created/direction:desc' => 'Newest first',
                '/sort:discovery_count/direction:desc' => 'Most discoveries',
                '/sort:favorite_count/direction:desc' => 'Most favorites',
                '/sort:total_rating/direction:desc' => 'Best rating'
            )
        ));
    ?>
    <div class="list-group">
        <?php foreach ($capsules as $capsule) : ?>
        <a href="#" class="list-group-item anchor-map-goto" data-id="<?php echo $capsule['Capsule']['id']; ?>" data-lat="<?php echo $capsule['Capsule']['lat']; ?>" data-lng="<?php echo $capsule['Capsule']['lng']; ?>">
            <span class="badge<?php echo ($favSorted) ? " alert-success" : " alert-info"; ?>">
                <span class="glyphicon glyphicon-star"></span><?php echo $capsule['Capsule']['favorite_count']; ?>
            </span>
            <span class="badge<?php echo ($discoverySorted) ? " alert-success" : " alert-info"; ?>">
                <span class="glyphicon glyphicon-map-marker"></span><?php echo $capsule['Capsule']['discovery_count']; ?>
            </span>
            <span class="badge<?php echo ($scoreSorted) ? " alert-success" : " alert-info"; ?>">
                <span class="glyphicon glyphicon-fire"></span><?php echo $capsule['Capsule']['total_rating']; ?>
            </span>
            <h4 class="list-group-item-heading text-format-overflow"><?php echo h($capsule['Capsule']['name']); ?></h4>
            <p class="list-group-item-text"><small><?php echo date('F j, Y, g:i a', strtotime($capsule['Capsule']['created'])); ?></small></p>
        </a>
        <?php endforeach; ?>
    </div>
    <?php echo $this->element('paginator_links'); ?>
</div>

<?php echo $this->Js->writeBuffer(); ?>