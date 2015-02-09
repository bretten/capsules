<div class="container">
    <h2>Login</h2>
    <?php echo $this->Form->create('User', array('role' => 'form')); ?>
        <?php
        echo $this->Form->input('username', array(
            'class' => 'form-control',
            'div' => 'form-group'
        ));
        echo $this->Form->input('password', array(
            'class' => 'form-control',
            'div' => 'form-group'
        ));
        ?>
    <div class="form-group">
        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </div>
</div>