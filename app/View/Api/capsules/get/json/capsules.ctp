<?php
$this->JsonResponse->addCapsulesToData($capsules, $this->request->query);
echo $this->JsonResponse->getResponseBodyJsonString();
