<?php

$documentUrl = env('DOCUMENT_BASE_URL', 'http://document-service.test');
$documentApi = env('DOCUMENT_BASE_API', 'api/document');
$billingUrl = env('BILLING_BASE_URL', 'http://billing-service.test');
$billingApi = env('BILLING_BASE_API', 'api/billing');
return [
    'document_url' => "$documentUrl/$documentApi/",
    'billing_url' => "$billingUrl/$billingApi/",
];
