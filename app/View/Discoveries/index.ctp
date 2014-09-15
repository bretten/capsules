<?php
    $this->Paginator->options(array(
        'update' => '#tab-pane-discoveries',
        'evalScripts' => true
    ));
?>

<div class="discoveries index">
    <table class="table table-striped">
    <tr>
            <th><?php echo $this->Paginator->sort('capsule_id', 'Name'); ?></th>
            <th><?php echo $this->Paginator->sort('favorite'); ?></th>
            <th><?php echo $this->Paginator->sort('rating'); ?></th>
            <th><?php echo $this->Paginator->sort('created'); ?></th>
            <th class="actions"><?php echo __('Actions'); ?></th>
    </tr>
    <?php foreach ($discoveries as $discovery): ?>
    <tr>
        <td>
            <?php echo $this->Html->link($discovery['Capsule']['name'], array('controller' => 'capsules', 'action' => 'view', $discovery['Capsule']['id'])); ?>
        </td>
        <td><?php echo h($discovery['Discovery']['favorite']); ?>&nbsp;</td>
        <td><?php echo h($discovery['Discovery']['rating']); ?>&nbsp;</td>
        <td><?php echo h($discovery['Discovery']['created']); ?>&nbsp;</td>
        <td class="actions">
            <a href="#" class="anchor-map-goto" data-lat="<?php echo $discovery['Capsule']['lat']; ?>" data-lng="<?php echo $discovery['Capsule']['lng']; ?>">Go To on Map</a>
            <?php echo $this->Html->link(__('Favorite'), array('action' => '#', $discovery['Discovery']['id'])); ?>
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