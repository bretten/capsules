<div class="pagination-container">
    <ul class="pagination">
    <?php
        echo $this->Paginator->prev(
            '<',
            array('escape' => false, 'tag' => 'li'),
            null,
            array('escape' => false, 'tag' => 'li', 'class' => 'disabled', 'disabledTag' => 'a')
        );
        echo $this->Paginator->numbers(
            array('separator' => '', 'modulus' => 4, 'tag' => 'li', 'currentTag' => 'a', 'currentClass' => 'active', 'first' => '<<', 'last' => '>>')
        );
        echo $this->Paginator->next(
            '>',
            array('escape' => false, 'tag' => 'li'),
            null,
            array('escape' => false, 'tag' => 'li', 'class' => 'disabled', 'disabledTag' => 'a')
        );
    ?>
    </ul>
</div>