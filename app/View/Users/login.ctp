<h3><?= __("Login"); ?></h3>
<hr>

<div class="row">
    <div class="col-md-12">
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
</div>