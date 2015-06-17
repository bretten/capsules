<script type="text/javascript">
    /**
     * Namespace
     */
    var capsuleEditor = {};

    /**
     * Capsule form
     */
    capsuleEditor.form = $('#CapsuleAddForm');

    /**
     * Validates the form input
     *
     * @param fileInput The file input
     */
    capsuleEditor.validateFileInput = function(fileInput) {
        // Will hold the error messages
        var fileErrors = [];
        // Validate the file
        var file = fileInput.prop('files')[0];
        if (typeof file !== 'undefined') {
            if (file.size > <?= Configure::read('Upload.Limit.Image'); ?>) {
                fileErrors.push("The file size cannot exceed 5MB");
            }
        } else {
            fileErrors.push("Please choose a file.");
        }
        return fileErrors;
    };

    /**
     * Listener for file input change
     *
     * @param e The event object
     */
    capsuleEditor.onFileInputChange = function(e) {
        // Find the container
        var container = $(this).closest('.memoir');
        // Find the preview div
        var previewContainer = container.find('.memoir-preview');
        // Hide the preview
        previewContainer.addClass('hidden');
        // Clear out any previous image
        previewContainer.attr('src', null);
        // Find the loader
        var loader = previewContainer.siblings('.modal-loader');
        // Validate the file
        var memoirFileErrorContainer = capsuleEditor.form.find("#Memoir0FileError");
        var fileErrors = capsuleEditor.validateFileInput($(this));
        if (fileErrors.length > 0) {
            memoirFileErrorContainer.html(fileErrors.join("<br>"));
            return;
        } else {
            memoirFileErrorContainer.html("");
        }
        // Get the file
        var file = $(this).prop('files')[0];
        // Get a FileReader
        var fileReader = new FileReader();
        // Read the file
        fileReader.readAsDataURL(file);
        // Show a progress indicator
        fileReader.onprogress = function(e) {
            loader.show();
        };
        // On load callback
        fileReader.onload = function(e) {
            previewContainer.attr('src', e.target.result);
            previewContainer.removeClass('hidden');
            loader.hide();
        };
    };

    /**
     * Listener for the form submit
     *
     * @param e The event object
     */
    capsuleEditor.onFormSubmit = function(e) {
        e.preventDefault();
        // Will hold the errors
        var capsuleErrors = [];
        var memoirTitleErrors = [];
        // Get the inputs
        var capsuleNameInput = capsuleEditor.form.find('#CapsuleName');
        var memoirTitleInput = capsuleEditor.form.find('#Memoir0Title');
        var memoirFileInput = capsuleEditor.form.find('#Memoir0File');
        // Validate the name
        if (!capsuleNameInput.val().trim()) {
            capsuleErrors.push("Please enter a name.");
        }
        if (capsuleNameInput.val().length > 255) {
            capsuleErrors.push("The name cannot exceed 255 characters.");
        }
        // Validate the memoir title
        if (!memoirTitleInput.val().trim()) {
            memoirTitleErrors.push("Please enter a title.");
        }
        if (memoirTitleInput.val().length > 255) {
            memoirTitleErrors.push("The title cannot exceed 255 characters.");
        }
        // Validate the file
        var memoirFileErrors = capsuleEditor.validateFileInput(memoirFileInput);
        // If there were no errors, submit the form
        if (capsuleErrors.length < 1 && memoirTitleErrors.length < 1 && memoirFileErrors.length < 1) {
            // Clear any errors
            capsuleEditor.form.find('.error-message').html("");
            capsuleEditor.form.submit();
        } else {
            // Display Capsule errors
            var capsuleNameErrorContainer = capsuleEditor.form.find("#CapsuleNameError");
            capsuleNameErrorContainer.html(capsuleErrors.join("<br>"));
            // Display Memoir errors
            var memoirTitleErrorContainer = capsuleEditor.form.find("#Memoir0TitleError");
            memoirTitleErrorContainer.html(memoirTitleErrors.join("<br>"));
            var memoirFileErrorContainer = capsuleEditor.form.find("#Memoir0FileError");
            memoirFileErrorContainer.html(memoirFileErrors.join("<br>"));
        }
    };

    $(document).ready(function() {
        // Handler for displaying thumbnail
        $(document).on('change', '.memoir-file', capsuleEditor.onFileInputChange);
        // Handler for the form submit
        $('button[type="submit"]').on('click', capsuleEditor.onFormSubmit);
    });
</script>
<div class="modal-header">
    <?php echo $this->Session->flash(); ?>
    <h4 class="modal-title text-format-overflow" id="modal-label-capsule-editor">
        <?php if (isset($capsuleName) && $capsuleName) : ?>
            <?php echo $capsuleName; ?>
        <?php else : ?>
            <?php echo __('New Capsule'); ?>
        <?php endif; ?>
    </h4>
    <?php echo $this->element('loader'); ?>
</div>
<div class="modal-body">
    <?php echo $this->Form->create('Capsule', array('id' => 'CapsuleAddForm', 'role' => 'form', 'type' => 'file')); ?>
    <?php
        echo $this->Form->input('name', array(
            'div' => 'form-group', 'class' => 'form-control',
            'after' => '<div class="error-message" id="CapsuleNameError"></div>'
        ));
        echo $this->Form->input('lat', array('type' => 'hidden'));
        echo $this->Form->input('lng', array('type' => 'hidden'));
    ?>
    <hr>
    <div id="memoirs">
        <div class="memoir" data-id="0">
            <?php
                echo $this->Form->input('Memoir.0.title', array(
                    'div' => 'form-group', 'class' => 'form-control',
                    'after' => '<div class="error-message" id="Memoir0TitleError"></div>'
                ));
            ?>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8">
                        <?php
                            echo $this->Form->input('Memoir.0.message', array(
                                'div' => 'form-group', 'class' => 'form-control',
                                'after' => '<div class="error-message" id="Memoir0MessageError"></div>'
                            ));
                            echo $this->Form->input('Memoir.0.file', array(
                                'div' => 'form-group', 'class' => 'memoir-file',
                                'type' => 'file', 'accepts' => 'image/png, image/jpeg, image/gif',
                                'after' => '<div class="error-message" id="Memoir0FileError"></div>'
                            ));
                        ?>
                    </div>
                    <div class="col-md-4">
                        <label for="MemoirPreview">Preview</label>
                        <?php echo $this->element('loader'); ?>
                        <img src="" class="memoir-preview img-responsive img-thumbnail hidden">
                    </div>
                </div>
            </div>
        </div>
        <hr>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-success btn-block">Save</button>
    </div>
</div>
