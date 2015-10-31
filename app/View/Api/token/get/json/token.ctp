<?php
$this->JsonResponse->addAuthTokenToData($token);
echo $this->JsonResponse->getResponseBodyJsonString();
