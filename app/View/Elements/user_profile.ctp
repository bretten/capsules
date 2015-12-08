<?php
$username = "";
$capsuleCount = 0;
$discoveryCount = 0;
// Make sure each value was passed in
if (isset($user) && isset($user['User'])) {
    // Username
    if (isset($user['User']['username'])) {
        $username = $user['User']['username'];
    }
    // Capsule count
    if (isset($user['User']['capsule_count'])) {
        $capsuleCount = $user['User']['capsule_count'];
    }
    // Discovery count
    if (isset($user['User']['discovery_count'])) {
        $discoveryCount = $user['User']['discovery_count'];
    }
}
?>

<h3>
    <span class="glyphicon glyphicon-user"></span>&nbsp;
    <?= $username; ?>
</h3>
<hr>

<div class="row">
    <div class="col-md-6">
        <h3>
            <span class="glyphicon glyphicon-map-marker"></span>&nbsp;
            <?= __("Capsules buried"); ?>
        </h3>
        <?= $capsuleCount; ?>
    </div>
    <div class="col-md-6">
        <h3>
            <span class="glyphicon glyphicon-map-marker"></span>&nbsp;
            <?= __("Capsules discovered"); ?>
        </h3>
        <?= $discoveryCount; ?>
    </div>
</div>
