<div class="capsules form">
<?php echo $this->Form->create('Capsule'); ?>
    <fieldset>
        <legend><?php echo __('Add Capsule'); ?></legend>
    <?php
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
        <li><?php echo $this->Html->link(__('List Capsules'), array('controller' => 'capsules', 'action' => 'index')); ?> </li>
    </ul>
</div>
