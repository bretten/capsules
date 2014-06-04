<div class="capsules form">
<?php echo $this->Form->create('Capsule'); ?>
    <fieldset>
        <legend><?php echo __('Edit Capsule'); ?></legend>
    <?php
        echo $this->Form->input('id');
        echo $this->Form->input('user_id');
        echo $this->Form->input('name');
        echo $this->Form->input('lat');
        echo $this->Form->input('lng');
    ?>
    </fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>

        <li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Capsule.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('Capsule.id'))); ?></li>
        <li><?php echo $this->Html->link(__('List Capsules'), array('action' => 'index')); ?></li>
        <li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
        <li><?php echo $this->Html->link(__('List Discoveries'), array('controller' => 'discoveries', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Discovery'), array('controller' => 'discoveries', 'action' => 'add')); ?> </li>
        <li><?php echo $this->Html->link(__('List Memoirs'), array('controller' => 'memoirs', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Memoir'), array('controller' => 'memoirs', 'action' => 'add')); ?> </li>
    </ul>
</div>
