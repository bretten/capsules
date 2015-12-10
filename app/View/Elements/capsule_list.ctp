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
        var capsuleModalLoadingIndicator = capsuleModal.find('.loading-indicator');
        var capsuleContainer = $('#<?= $containerId; ?>');

        // Open the Capsule modal
        $(document).on('click', '.capsule-list-item', function (e) {
            // Get the element
            var listItem = $(this);
            // Determine if the Capsule has not been opened
            var isUnopened = listItem.hasClass('list-group-item-warning');
            // Get the Capsule ID
            var capsuleId = listItem.data('id');
            // Send a request to get the Capsule content
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
                    // If the Capsule is unopened, mark it as opened
                    if (isUnopened) {
                        // Remove the highlight
                        listItem.removeClass('list-group-item-warning');
                        // Remove the image placeholder
                        listItem.find('.memoir-placeholder').remove();
                        // Show the preview
                        listItem.find('.img-thumbnail').removeClass('hidden');
                    }
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

        // Listener for clicking on the Capsule delete element
        $(document).on('click', '.confirm-delete-button', function (e) {
            // Get the ID
            var capsuleId = $(this).data('id');
            // Send the DELETE request
            $.ajax({
                type: 'DELETE',
                url: "/api/capsule/" + capsuleId,
                beforeSend: function (jqXHR, settings) {
                    // Show the loading indicator
                    capsuleModalLoadingIndicator.removeClass("hidden");
                },
                complete: function (jqXHR, textStatus) {
                    // Hide the loading indicator
                    capsuleModalLoadingIndicator.addClass("hidden");
                },
                success: function (data, textStatus, jqXHR) {
                    // Close the modal
                    capsuleModal.modal('hide');
                    // Trigger the Capsule deleted event
                    $(document).trigger('capsule:delete', [capsuleId]);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    capsuleModalContentContainer.html('<?= preg_replace('/\r|\n/', '', $this->element('modal_error_content')); ?>');
                }
            });
        });

        // Listener for a successful Capsule delete
        $(document).on('capsule:delete', function (e, capsuleId) {
            // Get the corresponding list item
            var listItem = capsuleContainer.find('.capsule-list-item[data-id="' + capsuleId + '"]');
            // Remove the markup
            listItem.remove();
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
