<?php

$composerJson = json_decode(file_get_contents(getcwd().'/app/composer.json'), true);


if (getenv('REVERSE')=='0') {
    if (!isset($composerJson['repositories']['local_php_bundle']))
        $composerJson['repositories']['local_php_bundle'] = [
            'type' => 'path',
            'url' => '/var/www/php_services_bundle',
        ];

    unset($composerJson['repositories']['65672704']);

    $composerJson['require']['test/php_services_bundle'] = 'dev-master';
} elseif (getenv('REVERSE')=='1') {
    if (isset($composerJson['repositories']['local_php_bundle'])) {
        unset($composerJson['repositories']['local_php_bundle']);
    }


    $composerJson['repositories']['65672704'] = [
        'type' => 'composer',
        'url' => 'https://gitlab.com/api/v4/group/65672704/-/packages/composer/',
    ];

    unset($composerJson['require']['test/php_services_bundle']);
} else {
    echo 'Please set REVERSE=1 or REVERSE=0';
    exit(1);
}

$resultJson = json_encode($composerJson, JSON_PRETTY_PRINT);
$resultJson = str_replace('\\/', '/', $resultJson);

file_put_contents(getcwd().'/app/composer.json', $resultJson);
