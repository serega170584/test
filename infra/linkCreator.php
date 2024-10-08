<?php

// otstante, eto govnokod chtobi devopsam legche bilo delat symlinki
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

function choosePath($dir = __DIR__, $dirsOnly = false)
{
    $input = '';
    while (true) {
        $files = scandir($dir);
        if ($dirsOnly) {
            foreach ($files as $key => $file) {
                if (is_file($dir . '/' . $file)) {
                    unset($files[$key]);
                }
            }
        }
        if ((string)(int)$input !== $input) {
            // it was search
            if ($input != '') {
                foreach ($files as $key => $file) {
                    if (substr($file, 0, strlen($input)) != $input) {
                        unset($files[$key]);
                    }
                }
            }
        } else {
            if ($input < 0) {
                //fast locations
                $dir = getFastLocationByNumber((int)$input);
            } else {
                $dir = realpath($dir . '/' . $files[(int)$input]);
            }

            if (is_dir($dir) && $input != 0) {
                $input = '';
                continue;
            } else {
                $chosenFile = $dir;
                break;
            }
        }

        echo 'Fast Locations: ' . "\n";
        foreach (getFastLocations() as $key => $file) {
            echo $key . ': ' . $file . "\n";
        }
        echo "\n";
        foreach ($files as $key => $file) {
            echo $key . ': ' . $file . "\n";
        }
        echo "\n";
        echo 'Current Directory: ' . $dir . "\n";
        echo 'Search string: "' . $input . '"' . "\n";
        $input = readline('Choose: ');
        echo "\n\n\n\n\n\n\n\n";
    }
    return $chosenFile;
}

//if (file_exists('infra')) {
//    $applicationContext = true;
//} else {
//    $applicationContext = false;
//}

$sourcePath = choosePath();

echo "\n\n\n";
echo ' == Source path: ' . $sourcePath . "\n";
echo "\n\n\n";
sleep(1);

$destinationLinkPath = choosePath(__DIR__ . '/projects', true);

echo ' == Destination directory: ' . $destinationLinkPath . "\n";

$destinationFileNameOrDirectoryName = readline('Type destination filename or path and filename or new path [empty if same as source]: ');

if ($destinationFileNameOrDirectoryName === '') {
    $exploded = explode('/', $sourcePath);
    $destinationFileNameOrDirectoryName = $exploded[count($exploded) - 1];
}

$destinationPath = $destinationLinkPath . '/' . $destinationFileNameOrDirectoryName;
echo 'We will create link from: ' . $sourcePath . "\n";

if (is_file($sourcePath)) {
    addToFastLocations(dirname($sourcePath));
} else {
    addToFastLocations($sourcePath);
}

echo 'To: ' . $destinationPath . "\n";
echo shell_exec('mkdir -p ' . dirname($destinationPath));

addToFastLocations(dirname($destinationPath));

$relativePath = relativePath($destinationPath, $sourcePath);
echo 'Relative path between first and second is: ' . $relativePath . "\n";
if (is_dir($sourcePath)) {
    $perDirOrPerFile = readline("Would you create links file per file[anything] or whole directory[nothing]? [anything or nothing] (default nothing): ");
    if ($perDirOrPerFile!='') {
        $files = scandir($sourcePath);
        shell_exec('mkdir -p '.dirname($destinationPath));
        shell_exec('rm -rf '.dirname($destinationPath).'/*');
        foreach ($files as $file) {
            if ($file=='.' || $file=='..') {
                continue;
            }
            echo 'Command is: ln -s ' . $relativePath.'/'.$file . ' ' . dirname($destinationPath).'/'.$file . "\n";
            echo shell_exec('ln -s ' . $relativePath.'/'.$file . ' ' . dirname($destinationPath).'/'.$file);
        }
    } else {
        echo 'Command is: ln -s ' . $relativePath . ' ' . $destinationPath . "\n";
        echo shell_exec('ln -s ' . $relativePath . ' ' . $destinationPath);
    }
} else {
    echo 'Command is: ln -s ' . $relativePath . ' ' . $destinationPath . "\n";
    echo shell_exec('ln -s ' . $relativePath . ' ' . $destinationPath);
}


function addToFastLocations($location)
{
    if (!file_exists('fast_locations')) {
        return;
    }
    if (trim($location) === '') {
        return;
    }
    $file = file('fast_locations');
    foreach ($file as $item) {
        if (trim($item) === trim($location)) {
            return;
        }
    }
    if (count($file) > 7) {
        unset($file[count($file) - 1]);
    }
    $newFile = [];
    $newFile[] = $location;
    foreach ($file as $item) {
        if (trim($item) === '') {
            continue;
        }
        $newFile[] = trim($item);
    }
    file_put_contents('fast_locations', implode("\n", $newFile));
}

function getFastLocations()
{
    if (!file_exists('fast_locations')) {
        return [];
    }
    $file = file('fast_locations');
    $locations = [];
    $i = -1;
    foreach ($file as $item) {
        $locations[$i--] = trim($item);
    }
    return $locations;
}

function getFastLocationByNumber($number)
{
    $locations = getFastLocations();
    return $locations[$number];
}
