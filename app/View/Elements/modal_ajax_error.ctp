<?php if (isset($includeStructure) && $includeStructure === true) : ?>
<div class="modal-header">
    <h4 class="modal-title" id="modal-label-capsule-list">Error</h4>
    <?php echo $this->element('loader'); ?>
</div>
<div class="modal-body">
<?php endif; ?>
    <?php
        echo $this->element('notification', array(
            'class' => 'alert-danger',
            'message' => 'The information could not be retrieved.  Please try again.'
        ));
    ?>
<?php if (isset($includeStructure) && $includeStructure === true) : ?>
</div>
<?php endif; ?>