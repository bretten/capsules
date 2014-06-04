<div class="capsules view">
<h2><?php echo __('Capsule'); ?></h2>
    <dl>
        <dt><?php echo __('Id'); ?></dt>
        <dd>
            <?php echo h($capsule['Capsule']['id']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('User'); ?></dt>
        <dd>
            <?php echo $this->Html->link($capsule['User']['id'], array('controller' => 'users', 'action' => 'view', $capsule['User']['id'])); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Name'); ?></dt>
        <dd>
            <?php echo h($capsule['Capsule']['name']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Lat'); ?></dt>
        <dd>
            <?php echo h($capsule['Capsule']['lat']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Lng'); ?></dt>
        <dd>
            <?php echo h($capsule['Capsule']['lng']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Created'); ?></dt>
        <dd>
            <?php echo h($capsule['Capsule']['created']); ?>
            &nbsp;
        </dd>
        <dt><?php echo __('Modified'); ?></dt>
        <dd>
            <?php echo h($capsule['Capsule']['modified']); ?>
            &nbsp;
        </dd>
    </dl>
</div>
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('Edit Capsule'), array('action' => 'edit', $capsule['Capsule']['id'])); ?> </li>
        <li><?php echo $this->Form->postLink(__('Delete Capsule'), array('action' => 'delete', $capsule['Capsule']['id']), array(), __('Are you sure you want to delete # %s?', $capsule['Capsule']['id'])); ?> </li>
        <li><?php echo $this->Html->link(__('List Capsules'), array('action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Capsule'), array('action' => 'add')); ?> </li>
        <li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
        <li><?php echo $this->Html->link(__('List Discoveries'), array('controller' => 'discoveries', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Discovery'), array('controller' => 'discoveries', 'action' => 'add')); ?> </li>
        <li><?php echo $this->Html->link(__('List Memoirs'), array('controller' => 'memoirs', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Memoir'), array('controller' => 'memoirs', 'action' => 'add')); ?> </li>
    </ul>
</div>
<div class="related">
    <h3><?php echo __('Related Discoveries'); ?></h3>
    <?php if (!empty($capsule['Discovery'])): ?>
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
    <?php foreach ($capsule['Discovery'] as $discovery): ?>
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
<div class="related">
    <h3><?php echo __('Related Memoirs'); ?></h3>
    <?php if (!empty($capsule['Memoir'])): ?>
    <table cellpadding = "0" cellspacing = "0">
    <tr>
        <th><?php echo __('Id'); ?></th>
        <th><?php echo __('Capsule Id'); ?></th>
        <th><?php echo __('File'); ?></th>
        <th><?php echo __('Message'); ?></th>
        <th><?php echo __('Order'); ?></th>
        <th><?php echo __('Created'); ?></th>
        <th><?php echo __('Modified'); ?></th>
        <th class="actions"><?php echo __('Actions'); ?></th>
    </tr>
    <?php foreach ($capsule['Memoir'] as $memoir): ?>
        <tr>
            <td><?php echo $memoir['id']; ?></td>
            <td><?php echo $memoir['capsule_id']; ?></td>
            <td><?php echo $memoir['file']; ?></td>
            <td><?php echo $memoir['message']; ?></td>
            <td><?php echo $memoir['order']; ?></td>
            <td><?php echo $memoir['created']; ?></td>
            <td><?php echo $memoir['modified']; ?></td>
            <td class="actions">
                <?php echo $this->Html->link(__('View'), array('controller' => 'memoirs', 'action' => 'view', $memoir['id'])); ?>
                <?php echo $this->Html->link(__('Edit'), array('controller' => 'memoirs', 'action' => 'edit', $memoir['id'])); ?>
                <?php echo $this->Form->postLink(__('Delete'), array('controller' => 'memoirs', 'action' => 'delete', $memoir['id']), array(), __('Are you sure you want to delete # %s?', $memoir['id'])); ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </table>
<?php endif; ?>

    <div class="actions">
        <ul>
            <li><?php echo $this->Html->link(__('New Memoir'), array('controller' => 'memoirs', 'action' => 'add')); ?> </li>
        </ul>
    </div>
</div>
