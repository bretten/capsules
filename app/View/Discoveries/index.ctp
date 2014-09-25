<?php
    $this->Paginator->options(array(
        'update' => '#tab-pane-discoveries',
        'evalScripts' => true,
        'before' => 'mapView.paginationUri.discoveries = $(this).attr("url")'
    ));
?>

<div class="discoveries index">
    <table class="table table-striped">
    <tr>
            <th><?php echo $this->Paginator->sort('Capsule.name', 'Name'); ?></th>
            <th><?php echo $this->Paginator->sort('favorite'); ?></th>
            <th><?php echo $this->Paginator->sort('rating'); ?></th>
            <th><?php echo $this->Paginator->sort('created'); ?></th>
            <th class="actions"><?php echo __('Actions'); ?></th>
    </tr>
    <?php foreach ($discoveries as $discovery): ?>
    <tr>
        <td>
            <a href="#" class="anchor-map-goto" data-id="<?php echo $discovery['Capsule']['id']; ?>" data-lat="<?php echo $discovery['Capsule']['lat']; ?>" data-lng="<?php echo $discovery['Capsule']['lng']; ?>">
                <?php echo h($discovery['Capsule']['name']); ?>
            </a>
        </td>
        <td><?php echo h($discovery['Discovery']['favorite']); ?>&nbsp;</td>
        <td>
            <?php if ($discovery['Discovery']['rating'] == 1) : ?>
                <span class="glyphicon glyphicon-chevron-up glyphicon-positive"></span>
            <?php elseif ($discovery['Discovery']['rating'] == -1) : ?>
                <span class="glyphicon glyphicon-chevron-down glyphicon-negative"></span>
            <?php else : ?>
                <span class="glyphicon glyphicon-minus glyphicon-neutral"></span>
            <?php endif; ?>
        </td>
        <td><?php echo h($discovery['Discovery']['created']); ?>&nbsp;</td>
        <td class="actions">
            <?php echo $this->Html->link(__('Rate'), array('action' => '#', $discovery['Discovery']['id'])); ?>
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