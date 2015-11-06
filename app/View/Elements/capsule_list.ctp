<?php
if (!isset($containerId)) {
    $containerId = "capsule-list-view";
}
?>

<script type="text/javascript">
    $(document).ready(function () {
        // Get the selectors
        var capsuleModal = $('#modal-capsule');
        var capsuleModalContentContainer = capsuleModal.find('#modal-capsule-content');
        var capsuleModalLoadingIndicator = capsuleModal.find('.loadingIndicator');

        // Open the Capsule modal
        $(document).on('click', '.capsule-list-item', function (e) {
            var capsuleId = $(this).data('id');
            $.ajax({
                type: 'GET',
                url: "/api/capsule/" + capsuleId,
                beforeSend: function (jqXHR, settings) {
                    // Show the loading indicator
                    capsuleModalLoadingIndicator.removeClass("hidden");
                    // Open the modal
                    capsuleModal.modal('show');
                },
                complete: function (jqXHR, textStatus) {
                    // Hide the loading indicator
                    capsuleModalLoadingIndicator.addClass("hidden");
                },
                success: function (data, textStatus, jqXHR) {
                    // Copy the markup from the response to the container
                    capsuleModalContentContainer.html(data);
                    // Open the modal
                    capsuleModal.modal('show');
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    capsuleModalContentContainer.html('<?= preg_replace('/\r|\n/', '', $this->element('modal_error_content')); ?>');
                }
            });
        });
    });
</script>

<?php if (isset($capsules) && is_array($capsules) && !empty($capsules)) : ?>
    <div class="list-group" id="<?= $containerId; ?>">
        <?= $this->element('capsule_list_item_collection', array('capsules' => $capsules)); ?>
    </div>
<?php endif; ?>

<div id="no-more-results-container" class="row hidden">
    <div class="col-md-12 text-center">
        <h3>
            <?= __("The end!"); ?>
            <small><?= __("There are no more results."); ?></small>
        </h3>
    </div>
</div>

<div class="row">
    <div class="col-md-12 text-center">
        <button type="button" class="btn btn-default" id="load-more-btn">
            <?= __("More"); ?> <?= $this->element('loading_indicator'); ?>
        </button>
    </div>
</div>

<?= $this->element('modal_capsule'); ?>
