<?php if ($isOwned || $discovery) : ?>

    <h1><?php echo $capsule['Capsule']['name']; ?></h1>

    <?php if ($isOwned) : ?>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-capsule-editor" data-id="<?php echo $capsule['Capsule']['id']; ?>">
        Edit
    </button>
    <?php endif; ?>

    <?php if ($discovery) : ?>
        <script type="text/javascript" src="/js/discovery_rater.js"></script>
        <?php echo $this->element('discovery_rater', array('id' => $discovery['Discovery']['id'], 'rating' => $discovery['Discovery']['rating'])); ?>
        <script type="text/javascript" src="/js/discovery_favorite_toggle.js"></script>
        <?php echo $this->element('discovery_favorite_toggle', array('id' => $discovery['Discovery']['id'], 'favorite' => $discovery['Discovery']['favorite'])); ?>
    <?php endif; ?>

<?php else : ?>

    <?php if (!$isReachable) : ?>
        <h3>You can see the Capsule in the distance, but you are still out of reach...</h3>
    <?php else : ?>
        <h3>The Capsule is sealed tight.  Try coming back later.</h3>
    <?php endif; ?>

<?php endif; ?>