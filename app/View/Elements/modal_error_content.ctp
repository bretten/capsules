<?php
// Set the message and title if they are not already set
if (!isset($title)) {
    $title = __("An error has occurred");
}
if (!isset($message)) {
    $message = __("Please try again.");
}
?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <h3 class="modal-title text-danger" id="modal-label-error">
        <?= $title; ?>
    </h3>
</div>
<div class="modal-body">
    <?= $message; ?>
</div>
