<div id="flashMessage" class="alert alert-dismissible<?php echo ((isset($class) && $class) ? " " . $class : ""); ?>" role="alert">
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
        <span class="sr-only">Close</span>
    </button>
    <?php echo h($message); ?>
</div>