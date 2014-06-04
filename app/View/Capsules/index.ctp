<div class="capsules index">
    <h2><?php echo __('Capsules'); ?></h2>
    <table cellpadding="0" cellspacing="0">
    <tr>
            <th><?php echo $this->Paginator->sort('id'); ?></th>
            <th><?php echo $this->Paginator->sort('user_id'); ?></th>
            <th><?php echo $this->Paginator->sort('name'); ?></th>
            <th><?php echo $this->Paginator->sort('lat'); ?></th>
            <th><?php echo $this->Paginator->sort('lng'); ?></th>
            <th><?php echo $this->Paginator->sort('created'); ?></th>
            <th><?php echo $this->Paginator->sort('modified'); ?></th>
            <th class="actions"><?php echo __('Actions'); ?></th>
    </tr>
    <?php foreach ($capsules as $capsule): ?>
    <tr>
        <td><?php echo h($capsule['Capsule']['id']); ?>&nbsp;</td>
        <td>
            <?php echo $this->Html->link($capsule['User']['id'], array('controller' => 'users', 'action' => 'view', $capsule['User']['id'])); ?>
        </td>
        <td><?php echo h($capsule['Capsule']['name']); ?>&nbsp;</td>
        <td><?php echo h($capsule['Capsule']['lat']); ?>&nbsp;</td>
        <td><?php echo h($capsule['Capsule']['lng']); ?>&nbsp;</td>
        <td><?php echo h($capsule['Capsule']['created']); ?>&nbsp;</td>
        <td><?php echo h($capsule['Capsule']['modified']); ?>&nbsp;</td>
        <td class="actions">
            <?php echo $this->Html->link(__('View'), array('action' => 'view', $capsule['Capsule']['id'])); ?>
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
    ?>  </p>
    <div class="paging">
    <?php
        echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
        echo $this->Paginator->numbers(array('separator' => ''));
        echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
    ?>
    </div>
</div>
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('New Capsule'), array('action' => 'add')); ?></li>
        <li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
        <li><?php echo $this->Html->link(__('List Discoveries'), array('controller' => 'discoveries', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Discovery'), array('controller' => 'discoveries', 'action' => 'add')); ?> </li>
        <li><?php echo $this->Html->link(__('List Memoirs'), array('controller' => 'memoirs', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Memoir'), array('controller' => 'memoirs', 'action' => 'add')); ?> </li>
    </ul>
</div>
