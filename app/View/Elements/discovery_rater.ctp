<?php
    // Determine the rating
    $isPositive = $isNegative = false;
    if (isset($rating) && $rating > 0) {
        $isPositive = true;
    } elseif (isset($rating) && $rating < 0) {
        $isNegative = true;
    }
?>

<div class="discovery-rater-container" data-id="<?php echo (isset($id) && $id) ? $id : 0; ?>">
    <div>
        <button type="button" class="discovery-rater-btn btn btn-default<?php echo ($isPositive) ? " btn-success" : ""; ?>" data-rating="1">
            <span class="glyphicon glyphicon-chevron-up"></span>
        </button>
    </div>
    <div>
        <?php echo (isset($score) && $score) ? $score : 0; ?>
    </div>
    <div>
        <button type="button" class="discovery-rater-btn btn btn-default<?php echo ($isNegative) ? " btn-danger" : ""; ?>" data-rating="-1">
            <span class="glyphicon glyphicon-chevron-down"></span>
        </button>
    </div>
</div>