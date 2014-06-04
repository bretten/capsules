<div class="users view">
<h2><?php echo __('User'); ?></h2>
    <dl>
        <dt><?php echo __('Id'); ?></dt>
        <dd>
            <?php echo h($user['User']['id']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Username'); ?></dt>
        <dd>
            <?php echo h($user['User']['username']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Email'); ?></dt>
        <dd>
            <?php echo h($user['User']['email']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Password'); ?></dt>
        <dd>
            <?php echo h($user['User']['password']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Created'); ?></dt>
        <dd>
            <?php echo h($user['User']['created']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Modified'); ?></dt>
        <dd>
            <?php echo h($user['User']['modified']); ?>
            &nbsp;
        </dd>
    </dl>
</div>
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('Edit User'), array('action' => 'edit', $user['User']['id'])); ?> </li>
        <li><?php echo $this->Form->postLink(__('Delete User'), array('action' => 'delete', $user['User']['id']), array(), __('Are you sure you want to delete # %s?', $user['User']['id'])); ?> </li>
        <li><?php echo $this->Html->link(__('List Users'), array('action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New User'), array('action' => 'add')); ?> </li>
        <li><?php echo $this->Html->link(__('List Capsules'), array('controller' => 'capsules', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Capsule'), array('controller' => 'capsules', 'action' => 'add')); ?> </li>
        <li><?php echo $this->Html->link(__('List Discoveries'), array('controller' => 'discoveries', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Discovery'), array('controller' => 'discoveries', 'action' => 'add')); ?> </li>
    </ul>
</div>
<div class="related">
    <h3><?php echo __('Related Capsules'); ?></h3>
    <?php if (!empty($user['Capsule'])): ?>
    <table cellpadding = "0" cellspacing = "0">
    <tr>
        <th><?php echo __('Id'); ?></th>
        <th><?php echo __('User Id'); ?></th>
        <th><?php echo __('Name'); ?></th>
        <th><?php echo __('Lat'); ?></th>
        <th><?php echo __('Lng'); ?></th>
        <th><?php echo __('Created'); ?></th>
        <th><?php echo __('Modified'); ?></th>
        <th class="actions"><?php echo __('Actions'); ?></th>
    </tr>
    <?php foreach ($user['Capsule'] as $capsule): ?>
        <tr>
            <td><?php echo $capsule['id']; ?></td>
            <td><?php echo $capsule['user_id']; ?></td>
            <td><?php echo $capsule['name']; ?></td>
            <td><?php echo $capsule['lat']; ?></td>
            <td><?php echo $capsule['lng']; ?></td>
            <td><?php echo $capsule['created']; ?></td>
            <td><?php echo $capsule['modified']; ?></td>
            <td class="actions">
                <?php echo $this->Html->link(__('View'), array('controller' => 'capsules', 'action' => 'view', $capsule['id'])); ?>
                <?php echo $this->Html->link(__('Edit'), array('controller' => 'capsules', 'action' => 'edit', $capsule['id'])); ?>
                <?php echo $this->Form->postLink(__('Delete'), array('controller' => 'capsules', 'action' => 'delete', $capsule['id']), array(), __('Are you sure you want to delete # %s?', $capsule['id'])); ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </table>
<?php endif; ?>

    <div class="actions">
        <ul>
            <li><?php echo $this->Html->link(__('New Capsule'), array('controller' => 'capsules', 'action' => 'add')); ?> </li>
        </ul>
    </div>
</div>
<div class="related">
    <h3><?php echo __('Related Discoveries'); ?></h3>
    <?php if (!empty($user['Discovery'])): ?>
    <table cellpadding = "0" cellspacing = "0">
    <tr>
        <th><?php echo __('Id'); ?></th>
        <th><?php echo __('Capsule Id'); ?></th>
        <th><?php echo __('User Id'); ?></th>
        <th><?php echo __('Favorite'); ?></th>
        <th><?php echo __('Rating'); ?></th>
        <th><?php echo __('Created'); ?></th>
        <th><?php echo __('Modified'); ?></th>
        <th class="actions"><?php echo __('Actions'); ?></th>
    </tr>
    <?php foreach ($user['Discovery'] as $discovery): ?>
        <tr>
            <td><?php echo $discovery['id']; ?></td>
            <td><?php echo $discovery['capsule_id']; ?></td>
            <td><?php echo $discovery['user_id']; ?></td>
            <td><?php echo $discovery['favorite']; ?></td>
            <td><?php echo $discovery['rating']; ?></td>
            <td><?php echo $discovery['created']; ?></td>
            <td><?php echo $discovery['modified']; ?></td>
            <td class="actions">
                <?php echo $this->Html->link(__('View'), array('controller' => 'discoveries', 'action' => 'view', $discovery['id'])); ?>
                <?php echo $this->Html->link(__('Edit'), array('controller' => 'discoveries', 'action' => 'edit', $discovery['id'])); ?>
                <?php echo $this->Form->postLink(__('Delete'), array('controller' => 'discoveries', 'action' => 'delete', $discovery['id']), array(), __('Are you sure you want to delete # %s?', $discovery['id'])); ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </table>
<?php endif; ?>

    <div class="actions">
        <ul>
            <li><?php echo $this->Html->link(__('New Discovery'), array('controller' => 'discoveries', 'action' => 'add')); ?> </li>
        </ul>
    </div>
</div>
