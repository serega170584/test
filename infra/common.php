<?php

function scanAllDir($dir) {
    $result = [];
    foreach(scandir($dir) as $filename) {
        if ($filename === '.' || $filename === '..') continue;
        $filePath = $dir . '/' . $filename;
        if (is_dir($filePath)) {
            $result[] = $filename;
            foreach (scanAllDir($filePath) as $childFilename) {
                $result[] = $filename . '/' . $childFilename;
            }
        } else {
            $result[] = $filename;
        }
    }
    return $result;
}

function prepareFiles($pwd) {
    $dir = 'projects/'.getenv("APP_NAME");
    $files = scanAllDir($dir);
    foreach ($files as &$file) {
        $file = $pwd.$dir.'/'.$file;
    }
    return $files;
}

function removeInfraPrefixIfNeeded($filePath) {
    if (!file_exists('infra')) {
        //we are in kobra
        $filePath = preg_replace('/^\/*infra\//', '', $filePath);
    }
    return $filePath;
}

function removeInfraPrefix($filePath) {
    return preg_replace('/^\/*infra\//', '', $filePath);
}

function relativePath($from, $to, $ps = DIRECTORY_SEPARATOR)
{
    $arFrom = explode($ps, rtrim($from, $ps));
    $arTo = explode($ps, rtrim($to, $ps));
    while (count($arFrom) && count($arTo) && ($arFrom[0] == $arTo[0])) {
        array_shift($arFrom);
        array_shift($arTo);
    }
    return str_pad("", (count($arFrom) - 1) * 3, '..' . $ps) . implode($ps, $arTo);
}
