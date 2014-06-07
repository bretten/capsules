<div class="users form">
    <?php echo $this->Form->create('User'); ?>
    <fieldset>
        <legend><?php echo AuthComponent::user('username'); ?></legend>
        <?php
        echo $this->Form->input('email');
        echo $this->element('change_password', array(
            'toggle_input' => 'change_password',
            'toggle_input_options' => array('type' => 'checkbox', 'format' => array('before', 'label', 'between', 'input', 'after', 'error')),
            'container' => array('id' => 'change_password'),
            'input' => 'password',
            'input_options' => array('value' => '', 'disabled' => 'disabled'),
            'confirm_input' => 'confirm_password',
            'confirm_input_options' => array('type' => 'password', 'value' => '', 'disabled' => 'disabled')
        ));
        ?>
    </fieldset>
    <?php echo $this->Form->end(__('Submit')); ?>
</div>