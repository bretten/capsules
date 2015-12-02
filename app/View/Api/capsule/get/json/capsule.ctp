<?php
// Add the Discovery data to the Capsule data
if (isset($discovery) && is_array($discovery)) {
    $capsule = array_merge($capsule, $discovery);
}

$this->JsonResponse->addCapsuleToData($capsule);
echo $this->JsonResponse->getResponseBodyJsonString();
