<?php
    // Determine the string to append to the class
    $classAppend = "";
    if (isset($class) && $class) {
        $classAppend .= " " . $class;
    }
    if (isset($dismissible) && $dismissible === true) {
        $classAppend .= " alert-dismissible";
    }
?>
<div id="flashMessage" class="alert<?php echo $classAppend; ?>" role="alert">
<?php if (isset($dismissible) && $dismissible === true) : ?>
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
        <span class="sr-only">Close</span>
    </button>
<?php endif; ?>
    <?php echo h($message); ?>
</div>