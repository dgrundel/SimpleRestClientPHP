<?php

require('SimpleRestClient.php');

$client = new SimpleRestClient('mysql', 'help_category', 'root');
$client->index();
