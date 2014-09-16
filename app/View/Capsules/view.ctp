<?php if ($isOwned || $discovery) : ?>

    <h1><?php echo $capsule['Capsule']['name']; ?></h1>
    <a href="#" class="anchor-map-goto" data-lat="<?php echo $capsule['Capsule']['lat']; ?>" data-lng="<?php echo $capsule['Capsule']['lng']; ?>">Go To on Map</a>

    <?php if ($isOwned) : ?>
    EDIT
    <?php endif; ?>

    <?php if ($discovery) : ?>
    RATE
    <?php endif; ?>

<?php else : ?>

    <?php if (!$isReachable) : ?>
        <h3>You can see the Capsule in the distance, but you are still out of reach...</h3>
    <?php else : ?>
        <h3>The Capsule is sealed tight.  Try coming back later.</h3>
    <?php endif; ?>

<?php endif; ?>