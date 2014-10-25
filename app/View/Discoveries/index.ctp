<?php
    $this->Paginator->options(array(
        'update' => '#tab-pane-discoveries',
        'evalScripts' => true,
        'before' => 'mapView.paginationUri.discoveries = $(this).attr("url")'
    ));
    $favSorted = isset($this->params['named']) && isset($this->params['named']['sort']) && $this->params['named']['sort'] === 'Capsule.favorite_count';
    $discoverySorted = isset($this->params['named']) && isset($this->params['named']['sort']) && $this->params['named']['sort'] === 'Capsule.discovery_count';
    $scoreSorted = isset($this->params['named']) && isset($this->params['named']['sort']) && $this->params['named']['sort'] === 'Capsule.total_rating';
?>

<div class="discoveries index">
    <?php
        echo $this->element('searcher', array(
            'container' => '#tab-pane-discoveries',
            'controller' => 'discoveries',
            'action' => 'index',
            'hasSearch' => true,
            'before' => 'mapView.paginationUri.discoveries = searcher.buildUri();',
            'sorts' => array(
                '/sort:Capsule.name/direction:asc' => 'A - Z',
                '/sort:Capsule.name/direction:desc' => 'Z - A',
                '/sort:Discovery.created/direction:desc' => 'Discovered recently',
                '/sort:Capsule.total_rating/direction:desc' => 'Best rating',
                '/sort:Capsule.discovery_count/direction:desc' => 'Most discoveries',
                '/sort:Capsule.favorite_count/direction:desc' => 'Most favorites',
            ),
            'filters' => array(
                Configure::read('Search.Filter.Favorite') => 'Favorited',
                Configure::read('Search.Filter.UpVote') => 'Up Voted',
                Configure::read('Search.Filter.DownVote') => 'Down Voted',
                Configure::read('Search.Filter.NoVote') => 'No Vote'
                
            )
        ));
    ?>
    <div class="list-group">
        <?php foreach ($discoveries as $discovery) : ?>
        <a href="#" class="list-group-item anchor-map-goto" data-id="<?php echo $discovery['Capsule']['id']; ?>" data-lat="<?php echo $discovery['Capsule']['lat']; ?>" data-lng="<?php echo $discovery['Capsule']['lng']; ?>">
            <div class="pull-right clearfix col-md-offset-1">
                <span class="badge<?php echo ($scoreSorted) ? " alert-success" : " alert-info"; ?>">
                    <span class="glyphicon glyphicon-fire"></span><?php echo $discovery['Capsule']['total_rating']; ?>
                </span>
                <span class="badge<?php echo ($discoverySorted) ? " alert-success" : " alert-info"; ?>">
                    <span class="glyphicon glyphicon-map-marker"></span><?php echo $discovery['Capsule']['discovery_count']; ?>
                </span>
                <span class="badge<?php echo ($favSorted) ? " alert-success" : " alert-info"; ?>">
                    <span class="glyphicon glyphicon-star"></span><?php echo $discovery['Capsule']['favorite_count']; ?>
                </span>
            </div>
            <h4 class="list-group-item-heading text-format-overflow">
                <?php if ($discovery['Discovery']['favorite']) : ?>
                    <span class="glyphicon glyphicon-star glyphicon-warning"></span>
                <?php else : ?>
                    <span class="glyphicon glyphicon-star glyphicon-neutral"></span>
                <?php endif; ?>
                <?php if ($discovery['Discovery']['rating'] == 1) : ?>
                    <span class="glyphicon glyphicon-chevron-up glyphicon-positive"></span>
                <?php elseif ($discovery['Discovery']['rating'] == -1) : ?>
                    <span class="glyphicon glyphicon-chevron-down glyphicon-negative"></span>
                <?php else : ?>
                    <span class="glyphicon glyphicon-minus glyphicon-neutral"></span>
                <?php endif; ?>
                <?php echo h($discovery['Capsule']['name']); ?>
            </h4>
            <p class="list-group-item-text"><small><?php echo date('F j, Y, g:i a', strtotime($discovery['Discovery']['created'])); ?></small></p>
        </a>
        <?php endforeach; ?>
    </div>
    <?php echo $this->element('paginator_links'); ?>
</div>

<?php echo $this->Js->writeBuffer(); ?>