<div class="discoveries view">
<h2><?php echo __('Discovery'); ?></h2>
    <dl>
        <dt><?php echo __('Id'); ?></dt>
        <dd>
            <?php echo h($discovery['Discovery']['id']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Capsule'); ?></dt>
        <dd>
            <?php echo $this->Html->link($discovery['Capsule']['name'], array('controller' => 'capsules', 'action' => 'view', $discovery['Capsule']['id'])); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('User'); ?></dt>
        <dd>
            <?php echo $this->Html->link($discovery['User']['id'], array('controller' => 'users', 'action' => 'view', $discovery['User']['id'])); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Favorite'); ?></dt>
        <dd>
            <?php echo h($discovery['Discovery']['favorite']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Rating'); ?></dt>
        <dd>
            <?php echo h($discovery['Discovery']['rating']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Created'); ?></dt>
        <dd>
            <?php echo h($discovery['Discovery']['created']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Modified'); ?></dt>
        <dd>
            <?php echo h($discovery['Discovery']['modified']); ?>
            &nbsp;
        </dd>
    </dl>
</div>
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('Edit Discovery'), array('action' => 'edit', $discovery['Discovery']['id'])); ?> </li>
        <li><?php echo $this->Form->postLink(__('Delete Discovery'), array('action' => 'delete', $discovery['Discovery']['id']), array(), __('Are you sure you want to delete # %s?', $discovery['Discovery']['id'])); ?> </li>
        <li><?php echo $this->Html->link(__('List Discoveries'), array('action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Discovery'), array('action' => 'add')); ?> </li>
        <li><?php echo $this->Html->link(__('List Capsules'), array('controller' => 'capsules', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Capsule'), array('controller' => 'capsules', 'action' => 'add')); ?> </li>
        <li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
    </ul>
</div>
