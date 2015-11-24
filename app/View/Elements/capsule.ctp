<div class="modal-header">
    <?php echo $this->Session->flash(); ?>
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
    <?php echo $this->element('loader'); ?>
</div>
<div class="modal-body">
    <div class="container-fluid">
        <?php foreach ($capsule['Memoir'] as $memoir) : ?>
            <div class="row">
                <div class="col-md-12">
                    <h3>
                        <?= $memoir['title']; ?>
                        <small><?= $memoir['message']; ?></small>
                    </h3>
                    <a href="/api/memoir/<?= $memoir['id']; ?>" class="thumbnail" target="_blank">
                        <img class="img-responsive" src="/api/memoir/<?= $memoir['id']; ?>"
                             alt="<?= $memoir['title']; ?>">
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($isOwned) : ?>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-capsule-editor"
                data-id="<?php echo $capsule['Capsule']['id']; ?>">
            Edit
        </button>
        <script type="text/javascript">
            $('#capsule-delete-btn').popover({
                content: $('<div/>', {
                    class: 'row'
                }).append(
                    $('<div/>', {
                        class: 'col-sm-6 text-center'
                    }).append(
                        $('<button/>', {
                            id: 'capsule-delete-confirm-btn',
                            class: 'btn btn-danger',
                            text: 'Yes',
                            'data-id': '<?php echo $capsule['Capsule']['id']; ?>'
                        })
                    )
                ).append(
                    $('<div/>', {
                        class: 'col-sm-6 text-center'
                    }).append(
                        $('<button/>', {
                            id: 'capsule-delete-cancel-btn',
                            class: 'btn btn-default',
                            text: 'No'
                        })
                    )
                ),
                html: true,
                placement: 'top',
                title: "Are you sure you want to delete this?"
            });
        </script>
        <button type="button" id="capsule-delete-btn" class="btn btn-danger">
            Delete
        </button>
    <?php endif; ?>
</div>
