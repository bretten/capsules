<?php
    $this->Paginator->options(array(
        'update' => '#tab-pane-discoveries',
        'evalScripts' => true,
        'before' => 'mapView.paginationUri.discoveries = $(this).attr("url")'
    ));
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
                '/sort:Capsule.favorite_count/direction:desc' => 'Most favorites',
                '/sort:Capsule.total_rating/direction:desc' => 'Best rating'
            ),
            'filters' => array(
                Configure::read('Search.Filter.Favorite') => 'Favorited',
                Configure::read('Search.Filter.UpVote') => 'Up Voted',
                Configure::read('Search.Filter.DownVote') => 'Down Voted',
                Configure::read('Search.Filter.NoVote') => 'No Vote'
                
            )
        ));
    ?>
    <table class="table table-striped">
    <tr>
            <th><?php echo $this->Paginator->sort('Capsule.name', 'Name'); ?></th>
            <th><?php echo $this->Paginator->sort('favorite_count'); ?></th>
            <th><?php echo $this->Paginator->sort('total_rating'); ?></th>
            <th><?php echo $this->Paginator->sort('created'); ?></th>
            <th class="actions"><?php echo __('Actions'); ?></th>
    </tr>
    <?php foreach ($discoveries as $discovery): ?>
    <tr>
        <td>
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
            <a href="#" class="anchor-map-goto" data-id="<?php echo $discovery['Capsule']['id']; ?>" data-lat="<?php echo $discovery['Capsule']['lat']; ?>" data-lng="<?php echo $discovery['Capsule']['lng']; ?>">
                <?php echo h($discovery['Capsule']['name']); ?>
            </a>
        </td>
        <td><?php echo h($discovery['Capsule']['favorite_count']); ?>&nbsp;</td>
        <td><?php echo h($discovery['Capsule']['total_rating']); ?>&nbsp;</td>
        <td><?php echo h($discovery['Discovery']['created']); ?>&nbsp;</td>
        <td class="actions">
            <?php echo $this->Html->link(__('Rate'), array('action' => '#', $discovery['Discovery']['id'])); ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </table>
    <?php echo $this->element('paginator_links'); ?>
</div>

<?php echo $this->Js->writeBuffer(); ?>