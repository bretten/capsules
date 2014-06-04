<div class="memoirs form">
<?php echo $this->Form->create('Memoir'); ?>
    <fieldset>
        <legend><?php echo __('Edit Memoir'); ?></legend>
    <?php
        echo $this->Form->input('id');
        echo $this->Form->input('capsule_id');
        echo $this->Form->input('file');
        echo $this->Form->input('message');
        echo $this->Form->input('order');
    ?>
    </fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>

        <li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Memoir.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('Memoir.id'))); ?></li>
        <li><?php echo $this->Html->link(__('List Memoirs'), array('action' => 'index')); ?></li>
        <li><?php echo $this->Html->link(__('List Capsules'), array('controller' => 'capsules', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Capsule'), array('controller' => 'capsules', 'action' => 'add')); ?> </li>
    </ul>
</div>
