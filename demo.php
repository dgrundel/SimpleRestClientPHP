<?php

require('SimpleRestClient.php');

$client = new SimpleRestClient('mysql', 'help_category', 'root');
$client->setIdColumnName('help_category_id');
$client->init();
