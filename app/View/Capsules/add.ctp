<script type="text/javascript" src="/js/Collection.js"></script>
<script type="text/javascript" src="/js/Memoir.js"></script>
<script type="text/javascript">
    // Namespace
    var capsule = {};

    capsule.memoirs = new Collection();

    <?php if (isset($this->request->data['Memoir']) && is_array($this->request->data['Memoir'])) : ?>
    <?php foreach ($this->request->data['Memoir'] as $key => $m) : ?>
        <?php echo "capsule.memoirs.add(new Memoir({$key}, '{$m['title']}', '{$m['file']}', '{$m['message']}', '{$m['order']}'));"; ?>
    <?php endforeach; ?>
    <?php endif; ?>

    $(document).ready(function() {
        // Handler for adding a Memoir
        $('.add').click(function(){
            var id = Date.now();
            if (capsule.memoirs.add(new Memoir(id))) {
                var container = $('<div/>', {
                    class: 'memoir',
                    'data-id': id
                });
                container.append($('<button/>', {
                    type: 'button',
                    class: 'remove',
                    text: '[x]',
                    'data-id': id
                }));

                var titleInput = $('<div/>', {
                    class: 'input text required'
                });
                titleInput.append($('<label/>', {
                    for: 'MemoirTitle' + id,
                    text: 'Title'
                }));
                titleInput.append($('<input/>', {
                    name: 'data[Memoir][' + id + '][title]',
                    type: 'text',
                    'data-id': id,
                    class: 'memoir-title',
                    id: 'MemoirTitle' + id
                }));

                var messageInput = $('<div/>', {
                    class: 'input text required'
                });
                messageInput.append($('<label/>', {
                    for: 'MemoirMessage' + id,
                    text: 'Message'
                }));
                messageInput.append($('<input/>', {
                    name: 'data[Memoir][' + id + '][message]',
                    type: 'text',
                    'data-id': id,
                    class: 'memoir-message',
                    id: 'MemoirMessage' + id
                }));

                var fileInput = $('<div/>', {
                    class: 'input text required'
                });
                fileInput.append($('<label/>', {
                    for: 'MemoirFile' + id,
                    text: 'File'
                }));
                fileInput.append($('<input/>', {
                    name: 'data[Memoir][' + id + '][file]',
                    type: 'text',
                    'data-id': id,
                    class: 'memoir-file',
                    id: 'MemoirFile' + id
                }));

                container.append(titleInput);
                container.append(messageInput);
                container.append(fileInput);

                $('#memoirs').append(container);

                $('html, body').animate({
                    scrollTop: container.offset().top
                }, 1000);
            }
        });
    });

    // Handler for removing a Memoir
    $(document).on('click', '.remove', function(e) {
        var id = $(this).attr('data-id');
        // Remove from the Achievement object
        capsule.memoirs.remove(id);
        // Remove the markup
        $(this).closest('.memoir').remove();
    });
</script>
<div class="capsules form">
<?php echo $this->Form->create('Capsule', array('id' => 'CapsuleAddForm')); ?>
    <fieldset>
        <legend><?php echo __('Add Capsule'); ?></legend>
    <?php
        if (isset($this->request->data['Capsule']['id']) && $this->request->data['Capsule']['id']) {
            echo $this->Form->input('id');
        }
        echo $this->Form->input('name');
        echo $this->Form->input('lat');
        echo $this->Form->input('lng');
    ?>
        <button type="button" class="add">Add Memoir</button>
        <div id="memoirs">
            <?php if (isset($this->request->data['Memoir']) && is_array($this->request->data['Memoir'])) : ?>
                <?php foreach ($this->request->data['Memoir'] as $key => $m) : ?>
                    <div class="memoir" data-id="<?php echo $key; ?>">
                        <button type="button" class="remove" data-id="<?php echo $key; ?>">[x]</button>
                        <?php
                        if (isset($m['id']) && $m['id']) {
                            echo $this->Form->input('Memoir.' . $key . '.id', array(
                                'type' => 'hidden',
                                'value' => $m['id'],
                                'data-id' => $key
                            ));
                        }
                        echo $this->Form->input('Memoir.' . $key . '.title', array(
                            'class' => 'memoir-title',
                            'value' => $m['title'],
                            'data-id' => $key
                        ));
                        echo $this->Form->input('Memoir.' . $key . '.message', array(
                            'class' => 'memoir-message',
                            'value' => $m['message'],
                            'data-id' => $key
                        ));
                        echo $this->Form->input('Memoir.' . $key . '.file', array(
                            'class' => 'memoir-file',
                            'value' => $m['file'],
                            'data-id' => $key
                        ));
                        ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('List Capsules'), array('controller' => 'capsules', 'action' => 'index')); ?> </li>
    </ul>
</div>
