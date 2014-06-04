<div class="discoveries form">
<?php echo $this->Form->create('Discovery'); ?>
    <fieldset>
        <legend><?php echo __('Edit Discovery'); ?></legend>
    <?php
        echo $this->Form->input('id');
        echo $this->Form->input('capsule_id');
        echo $this->Form->input('user_id');
        echo $this->Form->input('favorite');
        echo $this->Form->input('rating');
    ?>
    </fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>

        <li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Discovery.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('Discovery.id'))); ?></li>
        <li><?php echo $this->Html->link(__('List Discoveries'), array('action' => 'index')); ?></li>
        <li><?php echo $this->Html->link(__('List Capsules'), array('controller' => 'capsules', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Capsule'), array('controller' => 'capsules', 'action' => 'add')); ?> </li>
        <li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
    </ul>
</div>
