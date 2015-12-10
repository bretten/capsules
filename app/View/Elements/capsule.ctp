<?php
// Make sure the variables are set
if (!isset($discovery)) {
    $discovery = null;
}
if (!isset($isOwned)) {
    $isOwned = false;
}

// Determine the map URL
$mapUrl = Router::url(array(
    'controller' => 'capsules',
    'action' => 'map',
    '?' => array(
        'lat' => $capsule['Capsule']['lat'],
        'lng' => $capsule['Capsule']['lng'],
        'type' => $isOwned ? "Capsule" : "Discovery",
        'id' => $capsule['Capsule']['id']
    )
));
// Determine the user profile URL
$userUrl = "/user/" . $capsule['User']['username'];
?>

<?php if ($isOwned) : ?>
    <script type="text/javascript">
        // Declare the "namespace"
        var capsuleContent = {};

        // Selectors
        capsuleContent.deleteButton = $('#delete-anchor');
        capsuleContent.hideDeleteButton = $('#hide-delete-button');
        capsuleContent.deleteNotification = $('#delete-notification');

        // Listener for the delete button
        capsuleContent.deleteButton.on('click', function () {
            // If the delete notification is hidden, show it
            if (capsuleContent.deleteNotification.hasClass("hidden")) {
                capsuleContent.deleteNotification.removeClass("hidden");
            }
        });
        // Listener for the hide delete button
        capsuleContent.hideDeleteButton.on('click', function () {
            // If the delete notification is showing, hide it
            if (!capsuleContent.deleteNotification.hasClass("hidden")) {
                capsuleContent.deleteNotification.addClass("hidden");
            }
        });
    </script>
<?php endif; ?>

<div class="modal-header">
    <div class="row">
        <div class="col-md-12">
            <div class="pull-left">
                <div class="dropdown">
                    <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="true">
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="#" data-id="<?= $capsule['Capsule']['id']; ?>" data-dismiss="modal">
                                <?= __("Close"); ?>
                            </a>
                        </li>
                        <li class="dropdown-header"><?= __("Actions"); ?></li>
                        <li>
                            <a href="<?= $mapUrl; ?>" target="_blank">
                                <span class="glyphicon glyphicon-map-marker"></span>&nbsp;<?= __("Map"); ?>
                            </a>
                        </li>
                        <?php if ($isOwned) : ?>
                            <li>
                                <a href="#" id="delete-anchor">
                                    <span class="glyphicon glyphicon-trash"></span>&nbsp;<?= __("Delete"); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <h4 class="modal-title text-format-overflow" id="modal-label-capsule-info">
                &nbsp;<?php echo $capsule['Capsule']['name']; ?>
                <small>
                    <span>
                        <a href="<?= $userUrl; ?>" target="_blank">
                            <span class="glyphicon glyphicon-user"></span> <?= $capsule['User']['username']; ?>
                        </a>
                    </span>
                </small>
            </h4>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="text-left">
                <small>
                    <em>
                        <?= __("Buried on") . " " . date('F j, Y, g:i a',
                            strtotime($capsule['Capsule']['created'])); ?>
                    </em>
                </small>
            </div>
        </div>
    </div>
</div>
<div class="modal-body">
    <?php if ($isOwned) : ?>
        <div id="delete-notification" class="alert alert-danger hidden" role="alert">
            <strong><?= __("Warning!"); ?></strong>&nbsp;
            <?= __("Are you sure you want to dig up and destroy this Capsule?"); ?>
            <hr>
            <div class="row">
                <div class="col-md-6 pull-left">
                    <button type="button" id="hide-delete-button" class="btn btn-default">
                        <?= __("No, I want to leave it"); ?>
                    </button>
                </div>
                <div class="col-md-6 text-right">
                    <button type="button" class="btn btn-danger confirm-delete-button"
                            data-id="<?= $capsule['Capsule']['id']; ?>">
                        <?= __("Yes"); ?>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php foreach ($capsule['Memoir'] as $memoir) : ?>
        <div class="row">
            <div class="col-md-12">
                <a href="/api/memoir/<?= $memoir['id']; ?>" class="thumbnail" target="_blank">
                    <img class="img-responsive" src="/api/memoir/<?= $memoir['id']; ?>"
                         alt="<?= $memoir['title']; ?>">
                </a>

                <h3>
                    <?= $memoir['title']; ?>
                    <br>
                    <small><?= $memoir['message']; ?></small>
                </h3>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="modal-footer">
    <?php if ($discovery) : ?>
        <div class="row">
            <div class="col-md-12">
                <?php
                echo $this->element('discovery_rater', array(
                    'id' => $discovery['Discovery']['id'],
                    'rating' => $discovery['Discovery']['rating'],
                    'favorite' => $discovery['Discovery']['favorite']
                ));
                ?>
            </div>
        </div>
        <hr>
    <?php endif; ?>
    <div class="row text-center">
        <div class="col-md-4">
            <h5>
                <span class="glyphicon glyphicon-fire"></span>&nbsp;<?= __("Total rating"); ?>
            </h5>
            <?= $capsule['Capsule']['total_rating']; ?>
        </div>
        <div class="col-md-4">
            <h5>
                <span class="glyphicon glyphicon-map-marker"></span>&nbsp;<?= __("Times discovered"); ?>
            </h5>
            <?= $capsule['Capsule']['discovery_count']; ?>
        </div>
        <div class="col-md-4">
            <h5>
                <span class="glyphicon glyphicon-star"></span>&nbsp;<?= __("Times favorited"); ?>
            </h5>
            <?= $capsule['Capsule']['favorite_count']; ?>
        </div>
    </div>
</div>
