<?php
// Unique ID for form elements
$uniqueId = uniqid();
$searchInputId = "search-input-" . $uniqueId;
$searchButtonId = "search-button-" . $uniqueId;
$sortId = "sort-" . $uniqueId;
$filterName = "filter-" . $uniqueId;
$clearId = "clear-" . $uniqueId;
if (!isset($containerId)) {
    $containerId = "capsule-list-view-" . $uniqueId;
}
if (!isset($baseUri)) {
    $baseUri = "/capsules";
}
?>

<script type="text/javascript" src="/js/CapsuleSearcher.js"></script>
<script type="text/javascript">
    var capsuleSearcher = {};
    // Instantiate the CapsuleSearcher
    capsuleSearcher.searcher = new CapsuleSearcher();
    // Set the base URI
    capsuleSearcher.searcher.setBaseUri("<?= $baseUri; ?>");

    $(document).ready(function () {
        // Get all the selectors
        var searcherInputs = $('.searcher :input');
        var searchButton = $('#<?= $searchButtonId; ?>');
        var searchInput = $('#<?= $searchInputId; ?>');
        var sortInput = $('#<?= $sortId; ?>');
        var filterBtn = $('.filter-btn');
        var clearBtn = $('#<?= $clearId; ?>');
        var loadMoreButton = $("#load-more-btn");
        var loadingIndicators = $(".loading-indicator");
        var capsuleListContainer = $('#<?= $containerId; ?>');
        var noMoreResultsContainer = $("#no-more-results-container");

        // Function to reset the searcher but maintain the existing parameters
        capsuleSearcher.keepParametersAndResetToFirstPage = function () {
            // Clear the results container
            capsuleListContainer.html("");
            // Reset the page number and get the results
            capsuleSearcher.searcher.resetPageNumber();
            capsuleSearcher.searcher.getResults();
        };

        // Function to disable or enable the search
        capsuleSearcher.setDisabledStateOnSearchInputs = function (disabledState) {
            // Set the state on all inputs within the searcher
            searcherInputs.prop('disabled', disabledState);
            // Set the state on the "load more" button
            loadMoreButton.prop('disabled', disabledState);
        };

        // Set the beforeSend callback
        capsuleSearcher.searcher.setBeforeSendCallback(function (jqXHR, settings) {
            // Disable all searcher inputs
            capsuleSearcher.setDisabledStateOnSearchInputs(/* disabledState */ true);
            // Show loading indicators
            loadingIndicators.removeClass("hidden");
        });
        // Set the complete callback
        capsuleSearcher.searcher.setCompleteCallback(function (jqXHR, textStatus) {
            // Enable all searcher inputs
            capsuleSearcher.setDisabledStateOnSearchInputs(/* disabledState */ false);
            // Hide loading indicators
            loadingIndicators.addClass("hidden");
        });
        // Set the success callback
        capsuleSearcher.searcher.setSuccessCallback(function (data, textStatus, jqXHR) {
            // Check if there were any results markup
            if (data.trim()) {
                // Hide message indicating there are no more results
                noMoreResultsContainer.addClass("hidden");
                // Append the results markup
                capsuleListContainer.append(data);
            } else {
                // Show message indicating there are no more results
                noMoreResultsContainer.removeClass("hidden");
                // Decrement the page count since the request returned no results
                capsuleSearcher.searcher.decrementPage();
            }
        });
        // Set the error callback
        capsuleSearcher.searcher.setErrorCallback(function (jqXHR, textStatus, errorThrown) {
            // Show the modal error
            $('#modal-error').modal('show');
            // Decrement the page count since the request returned no results
            capsuleSearcher.searcher.decrementPage();
        });

        // Listener for the search button
        searchButton.on('click', function (e) {
            capsuleSearcher.searcher.setSearchString(searchInput.val());
            capsuleSearcher.keepParametersAndResetToFirstPage();
        });

        // Listener for the search input
        searchInput.on('keypress', function (e) {
            // See if the event was triggered by the "enter" or "tab" keys
            if (e.keyCode === 13 || e.keyCode === 9) {
                capsuleSearcher.searcher.setSearchString($(this).val());
                capsuleSearcher.keepParametersAndResetToFirstPage();
            }
        });

        // Listener for the sort input
        sortInput.on('change', function (e) {
            capsuleSearcher.searcher.setSortKey($(this).val());
            capsuleSearcher.keepParametersAndResetToFirstPage();
        });

        // Listener for the filter input
        filterBtn.on('click', function (e) {
            var currentBtn = $(this);
            if (currentBtn.hasClass('active')) {
                // Un-select all filters
                filterBtn.removeClass('active');
                // Re-search without filters
                capsuleSearcher.searcher.setFilterKey(null);
            } else {
                // Un-select all filters
                filterBtn.removeClass('active');
                // Select the filter that was clicked
                currentBtn.addClass('active');
                // Search with the selected filter
                capsuleSearcher.searcher.setFilterKey(currentBtn.val());
            }
            capsuleSearcher.keepParametersAndResetToFirstPage();
        });

        // Listener to prevent focusing on the filter buttons
        filterBtn.on('mouseup', function (e) {
            $(this).blur();
        });

        // Listener for the clear button
        clearBtn.on('click', function (e) {
            // Reset inputs
            searchInput.val("");
            sortInput.val("");
            filterBtn.blur().removeClass('active');
            // Clear the result container
            capsuleListContainer.html("");
            // Reset the query params and get the default results
            capsuleSearcher.searcher.resetQueryParams();
            capsuleSearcher.searcher.getResults();
        });

        // Listener for the "Load more" button
        loadMoreButton.on('click', function (e) {
            capsuleSearcher.searcher.incrementPage();
            capsuleSearcher.searcher.getResults();
        });
    });
</script>

<div class="searcher">

    <div class="row">
        <div class="form-group col-md-6">
            <label><?= __("Search"); ?></label>

            <div class="input-group">
                <input type="text" class="form-control" placeholder="<?= __("Keywords"); ?>"
                       id="<?= $searchInputId; ?>">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-default" id="<?= $searchButtonId; ?>">
                        <span class="glyphicon glyphicon-search"></span>
                    </button>
                </span>
            </div>
        </div>

        <?php if (isset($sorts) && is_array($sorts) && !empty($sorts)) : ?>
            <div class="form-group col-md-3">
                <label><?= __("Sort by"); ?></label>
                <select class="form-control" id="<?= $sortId; ?>">
                    <option value=""></option>
                    <?php foreach ($sorts as $sortKey => $text) : ?>
                        <option value="<?= $sortKey; ?>"><?= $text; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <?php if (isset($filters) && is_array($filters) && !empty($filters)) : ?>
            <div class="form-group col-md-2">
                <label><?= __("Filter by"); ?></label>

                <div class="btn-group btn-group-justified" role="group">
                    <?php foreach ($filters as $filterKey => $filter) : ?>
                        <?php
                        if (!is_array($filter) || !isset($filter['text']) || !isset($filter['iconClass'])) {
                            continue;
                        }
                        ?>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-default filter-btn" value="<?= $filterKey; ?>">
                                <span class="<?= $filter['iconClass']; ?>"></span>
                                <span class="visible-xs-inline-block visible-sm-inline-block">
                                    <?= $filter['text']; ?>
                                </span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="form-group col-md-1">
            <label><?= __("Reset"); ?></label>
            <button type="button" class="btn btn-default btn-block" id="<?= $clearId; ?>">
                <span class="glyphicon glyphicon-remove"></span>
                <span class="visible-xs-inline-block visible-sm-inline-block"><?= __("Clear"); ?></span>
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 text-center">
            <?= $this->element('loading_indicator'); ?>
        </div>
    </div>

</div>

<?= $this->element('modal_error',
    array("message" => __("There was a problem loading the results.  Please try again."))); ?>
