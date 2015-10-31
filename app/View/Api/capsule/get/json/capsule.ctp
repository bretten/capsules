<?php
// Add the Discovery data to the Capsule data
$capsule = array_merge($capsule, $discovery);
$this->JsonResponse->addCapsuleToData($capsule);
echo $this->JsonResponse->getResponseBodyJsonString();
