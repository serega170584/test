<?php

$composerJson = json_decode(file_get_contents(getcwd().'/app/composer.json'), true);

$composerJson['autoload']['psr-4'][getenv('PHP_NAMESPACE').'\\'] = 'phpGenerated/'.getenv('PHP_NAMESPACE').'/';

$resultJson = json_encode($composerJson, JSON_PRETTY_PRINT);
$resultJson = str_replace('\\/', '/', $resultJson);

file_put_contents(getcwd().'/app/composer.json', $resultJson);