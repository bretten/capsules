<?php
    $this->Paginator->options(array(
        'update' => '#tab-pane-capsules',
        'evalScripts' => true,
        'before' => 'mapView.paginationUri.capsules = $(this).attr("url")'
    ));
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
                '/sort:favorite_count/direction:desc' => 'Most favorites',
                '/sort:total_rating/direction:desc' => 'Best rating'
            )
        ));
    ?>
    <table class="table table-striped">
    <tr>
            <th><?php echo $this->Paginator->sort('name'); ?></th>
            <th><?php echo $this->Paginator->sort('favorite_count'); ?></th>
            <th><?php echo $this->Paginator->sort('total_rating'); ?></th>
            <th><?php echo $this->Paginator->sort('created'); ?></th>
            <th class="actions"><?php echo __('Actions'); ?></th>
    </tr>
    <?php foreach ($capsules as $capsule): ?>
    <tr>
        <td>
            <a href="#" class="anchor-map-goto" data-id="<?php echo $capsule['Capsule']['id']; ?>" data-lat="<?php echo $capsule['Capsule']['lat']; ?>" data-lng="<?php echo $capsule['Capsule']['lng']; ?>">
                <?php echo h($capsule['Capsule']['name']); ?>
            </a>
        </td>
        <td><?php echo h($capsule['Capsule']['favorite_count']); ?>&nbsp;</td>
        <td><?php echo h($capsule['Capsule']['total_rating']); ?>&nbsp;</td>
        <td><?php echo h($capsule['Capsule']['created']); ?>&nbsp;</td>
        <td class="actions">
            <?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $capsule['Capsule']['id'])); ?>
            <?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $capsule['Capsule']['id']), array(), __('Are you sure you want to delete # %s?', $capsule['Capsule']['id'])); ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </table>
    <?php echo $this->element('paginator_links'); ?>
</div>

<?php echo $this->Js->writeBuffer(); ?>