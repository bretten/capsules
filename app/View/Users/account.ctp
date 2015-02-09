<div class="container">
    <h2><?php echo AuthComponent::user('username'); ?></h2>
    <?php echo $this->Form->create('User', array('role' => 'form')); ?>
        <?php
        echo $this->Form->input('email', array('div' => 'form-group', 'class' => 'form-control'));
        echo $this->element('change_password', array(
            'toggle_input' => 'change_password',
            'toggle_input_options' => array(
                'type' => 'checkbox',
                'div' => 'form-group',
                'class' => 'checkbox',
                'autocomplete' => 'off',
                'format' => array('before', 'label', 'between', 'input', 'after', 'error')
            ),
            'container' => array(
                'id' => 'change_password',
                'class' => 'well well-lg'
            ),
            'input' => 'password',
            'input_options' => array(
                'value' => '',
                'disabled' => 'disabled',
                'div' => 'form-group',
                'class' => 'form-control'
            ),
            'confirm_input' => 'confirm_password',
            'confirm_input_options' => array(
                'type' => 'password',
                'value' => '',
                'disabled' => 'disabled',
                'div' => 'form-group',
                'class' => 'form-control'
            )
        ));
        ?>
    <div class="form-group">
        <button type="submit" class="btn btn-success btn-block">Save</button>
    </div>
</div>