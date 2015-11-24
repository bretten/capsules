<?php
// Make sure the variables are set
if (!isset($discovery)) {
    $discovery = null;
}
?>

<div class="modal-header">
    <div class="row">
        <div class="col-md-8">
            <h4 class="modal-title text-format-overflow"
                id="modal-label-capsule-info"><?php echo $capsule['Capsule']['name']; ?></h4>
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
</div>
<div class="modal-body">
    <div class="container-fluid">
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
</div>
