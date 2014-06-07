<script type="text/javascript" src="/js/Collection.js"></script>
<script type="text/javascript" src="/js/Memoir.js"></script>
<script type="text/javascript">
    // Namespace
    var capsule = {};

    capsule.memoirs = new Collection();

    <?php if (isset($this->request->data['Memoir']) && is_array($this->request->data['Memoir'])) : ?>
    <?php foreach ($this->request->data['Memoir'] as $key => $m) : ?>
        <?php echo "capsule.memoirs.add(new Memoir({$key}, '{$m['message']}', '{$m['message']}', '{$m['message']}'));"; ?>
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

                var messageInput = $('<div/>', {
                    class: 'input text required'
                });
                messageInput.append($('<label/>', {
                    for: 'MemoirMessage' + id,
                    text: 'Message'
                }));
                messageInput.append($('<input/>', {
                    type: 'text',
                    'data-id': id,
                    class: 'memoir-message',
                    id: 'MemoirMessage' + id
                }));

                container.append(messageInput);

                $('#memoirs').append(container);

                $('html, body').animate({
                    scrollTop: container.offset().top
                }, 1000);
            }
        });

        // Handler for form submit
        $('#CapsuleAddForm').submit(function(e) {
            var memoirs = capsule.memoirs.getSet();
            for (var i = 0; i < memoirs.length; i++) {
                var memoir = memoirs[i];

                $('<input/>').attr({
                    type: 'hidden',
                    name: 'data[Memoir][' + i + '][message]',
                    value: $('.memoir-message[data-id="' + memoir.getID() + '"]').val()
                }).appendTo($(this));
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
<?php echo $this->Form->create('Capsule'); ?>
    <fieldset>
        <legend><?php echo __('Add Capsule'); ?></legend>
    <?php
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
                        <div class="input text required">
                            <label for="MemoirMessage<?php echo $key; ?>">Message</label>
                            <input type="text" data-id="<?php echo $key; ?>" class="memoir-message" id="MemoirMessage<?php echo $key; ?>" value="<?php echo $m['message']; ?>" />
                        </div>
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
