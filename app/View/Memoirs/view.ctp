<div class="memoirs view">
<h2><?php echo __('Memoir'); ?></h2>
    <dl>
        <dt><?php echo __('Id'); ?></dt>
        <dd>
            <?php echo h($memoir['Memoir']['id']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Capsule'); ?></dt>
        <dd>
            <?php echo $this->Html->link($memoir['Capsule']['name'], array('controller' => 'capsules', 'action' => 'view', $memoir['Capsule']['id'])); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('File'); ?></dt>
        <dd>
            <?php echo h($memoir['Memoir']['file']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Message'); ?></dt>
        <dd>
            <?php echo h($memoir['Memoir']['message']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Order'); ?></dt>
        <dd>
            <?php echo h($memoir['Memoir']['order']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Created'); ?></dt>
        <dd>
            <?php echo h($memoir['Memoir']['created']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Modified'); ?></dt>
        <dd>
            <?php echo h($memoir['Memoir']['modified']); ?>
            &nbsp;
        </dd>
    </dl>
</div>
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('Edit Memoir'), array('action' => 'edit', $memoir['Memoir']['id'])); ?> </li>
        <li><?php echo $this->Form->postLink(__('Delete Memoir'), array('action' => 'delete', $memoir['Memoir']['id']), array(), __('Are you sure you want to delete # %s?', $memoir['Memoir']['id'])); ?> </li>
        <li><?php echo $this->Html->link(__('List Memoirs'), array('action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Memoir'), array('action' => 'add')); ?> </li>
        <li><?php echo $this->Html->link(__('List Capsules'), array('controller' => 'capsules', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Capsule'), array('controller' => 'capsules', 'action' => 'add')); ?> </li>
    </ul>
</div>
