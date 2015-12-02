<?php
// Make sure the variables are set
if (!isset($discovery)) {
    $discovery = null;
}
if (!isset($isOwned)) {
    $isOwned = false;
}
?>

<div class="modal-header">
    <div class="row">
        <div class="col-md-8">
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
                        <?php if ($isOwned) : ?>
                            <li class="dropdown-header"><?= __("Actions"); ?></li>
                            <li>
                                <a href="#" class="capsule-delete-anchor" data-id="<?= $capsule['Capsule']['id']; ?>">
                                    <span class="glyphicon glyphicon-trash"></span>&nbsp;<?= __("Delete"); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <h4 class="modal-title text-format-overflow" id="modal-label-capsule-info">
                &nbsp;<?php echo $capsule['Capsule']['name']; ?>
            </h4>
        </div>
        <div class="col-md-4 pull-right">
            <?php if ($discovery) : ?>
                <?php
                echo $this->element('discovery_rater', array(
                    'id' => $discovery['Discovery']['id'],
                    'rating' => $discovery['Discovery']['rating'],
                    'favorite' => $discovery['Discovery']['favorite']
                ));
                ?>
            <?php endif; ?>
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
            <div class="text-center">
                <span class="badge alert-info">
                    <span class="glyphicon glyphicon-fire"></span><?= $capsule['Capsule']['total_rating']; ?>
                </span>
                <span class="badge alert-info">
                    <span
                        class="glyphicon glyphicon-map-marker"></span><?= $capsule['Capsule']['discovery_count']; ?>
                </span>
                <span class="badge alert-info">
                    <span class="glyphicon glyphicon-star"></span><?= $capsule['Capsule']['favorite_count']; ?>
                </span>
            </div>
        </div>
    </div>
</div>
<div class="modal-body">
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
