<?php
    $this->Paginator->options(array(
        'update' => '#tab-pane-capsules',
        'evalScripts' => true,
        'before' => 'mapView.paginationUri.capsules = $(this).attr("url")'
    ));
?>

<div class="capsules index">
    <table class="table table-striped">
    <tr>
            <th><?php echo $this->Paginator->sort('name'); ?></th>
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
        <td><?php echo h($capsule['Capsule']['created']); ?>&nbsp;</td>
        <td class="actions">
            <?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $capsule['Capsule']['id'])); ?>
            <?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $capsule['Capsule']['id']), array(), __('Are you sure you want to delete # %s?', $capsule['Capsule']['id'])); ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </table>
    <p>
    <?php
        echo $this->Paginator->counter(array(
            'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
        ));
    ?>
    </p>
    <div class="paging">
    <?php
        echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
        echo $this->Paginator->numbers(array('separator' => ''));
        echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
    ?>
    </div>
</div>

<?php echo $this->Js->writeBuffer(); ?>