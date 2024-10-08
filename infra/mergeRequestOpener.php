<?php
const GET = 1;
const POST = 2;
const PUT = 3;
const DELETE = 4;
/**
 * @param string $url
 * @param array $data
 * @param int $method
 * @param string $privateToken
 * @return mixed
 */
function sendRequest($url, $data, $method = POST, $privateToken = '')
{
    $params = http_build_query($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    if ($method !== GET) {
        if ($method===PUT) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        }
        if ($method===DELETE) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $urlWithParams = $url;
    } else {
        $urlWithParams = $url . '?' . $params;
    }

    curl_setopt($ch, CURLOPT_URL, $urlWithParams);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($privateToken!=='') {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'PRIVATE-TOKEN: '.$privateToken
        ]);
    }
    $result = curl_exec($ch);
    $responseJson = json_decode($result, true);

    curl_close($ch);

    return $responseJson;
}

$host = 'https://gitlab.com/api/v4/projects/';

if (getenv('SKIP_CLOSING_OLD_REQUESTS')==1) {

} else {
    $result = sendRequest($host.getenv('CI_PROJECT_ID').'/merge_requests', ['state'=>'opened', 'labels'=>'newInfra'], GET, getenv('INFRA_PUSH_TOKEN'));
    if (count($result)>0) {
        foreach ($result as $item) {
            $mrResult = sendRequest($host.getenv('CI_PROJECT_ID').'/merge_requests/'.$item['iid'], ['state_event'=>'close', 'remove_source_branch'=>true], PUT, getenv('INFRA_PUSH_TOKEN'));
            sendRequest($host.getenv('CI_PROJECT_ID').'/repository/branches/'.$item['source_branch'], [], DELETE, getenv('INFRA_PUSH_TOKEN'));
        }
    }
}

////todo temporarily fixing not needed branches
//$result = sendRequest($host.getenv('CI_PROJECT_ID').'/merge_requests', ['state'=>'closed'], GET, getenv('INFRA_PUSH_TOKEN'));
//if (count($result)>0) {
//    foreach ($result as $item) {
//        sendRequest($host.getenv('CI_PROJECT_ID').'/repository/branches/'.$item['source_branch'], [], DELETE, getenv('INFRA_PUSH_TOKEN'));
//    }
//}
if (isset($argv[1]) && ($argv[1] == 'draft')) {
    $title = 'Draft: ' . 'Infra update: ' . getenv('REF_NAME');
} else {
    $title = 'Infra update: ' . getenv('REF_NAME');
}
$creatingRequestData = [
    'id'=> getenv('CI_PROJECT_ID'),
    'source_branch'=> getenv('REF_NAME'),
    'target_branch'=> getenv('TARGET_BRANCH'),
    'assignee_id'=> getenv('ASSIGN_MERGE_REQUEST_TO'),
    'remove_source_branch'=> true,
    'title'=> $title,
    'labels'=> 'newInfra',
];
$mrCreationResult = sendRequest($host.getenv('CI_PROJECT_ID').'/merge_requests/', $creatingRequestData, POST, getenv('INFRA_PUSH_TOKEN'));
for ($i=0; $i<10; $i++) {
    $mrChanges = sendRequest($host.getenv('CI_PROJECT_ID').'/merge_requests/'.$mrCreationResult['iid'].'/diffs', [], GET, getenv('INFRA_PUSH_TOKEN'));
    $i++;
    if (count($mrChanges)>0) {
        break;
    }
    sleep(5);
}

if (count($mrChanges)==0) {
    sendRequest($host.getenv('CI_PROJECT_ID').'/merge_requests/'.$mrCreationResult['iid'], ['state_event'=>'close', 'remove_source_branch'=>true], PUT, getenv('INFRA_PUSH_TOKEN'));
    sendRequest($host.getenv('CI_PROJECT_ID').'/repository/branches/'.$mrCreationResult['source_branch'], [], DELETE, getenv('INFRA_PUSH_TOKEN'));
    exit(0);
}

if (isset($argv[1]) && $argv[1]=='draft') {
    if (count($mrChanges)==1) {
        if ($mrChanges[0]['new_path']=='Makefile') {
            sendRequest($host.getenv('CI_PROJECT_ID').'/merge_requests/'.$mrCreationResult['iid'], ['state_event'=>'close', 'remove_source_branch'=>true], PUT, getenv('INFRA_PUSH_TOKEN'));
            sendRequest($host.getenv('CI_PROJECT_ID').'/repository/branches/'.$mrCreationResult['source_branch'], [], DELETE, getenv('INFRA_PUSH_TOKEN'));
            echo "skip";
            exit(0);
        }
    }
    sendRequest($host.getenv('CI_PROJECT_ID').'/merge_requests/'.$mrCreationResult['iid'], ['state_event'=>'close', 'remove_source_branch'=>true], PUT, getenv('INFRA_PUSH_TOKEN'));
    sendRequest($host.getenv('CI_PROJECT_ID').'/repository/branches/'.$mrCreationResult['source_branch'], [], DELETE, getenv('INFRA_PUSH_TOKEN'));

    echo "no-skip";
    exit(0);
} else {
    echo "ok";
    exit(0);
}
