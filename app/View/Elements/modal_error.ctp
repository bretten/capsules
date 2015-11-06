<div id="modal-error" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-label-error"
     aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <?= $this->element('modal_error_content', array('message' => $message, 'title' => $title)); ?>
        </div>
    </div>
</div>
