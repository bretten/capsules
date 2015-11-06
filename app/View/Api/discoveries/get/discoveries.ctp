<?php if (isset($capsules) && is_array($capsules) && !empty($capsules)) : ?>
    <?php echo $this->element('capsule_list_item_collection', array('capsules' => $capsules)); ?>
<?php endif;
