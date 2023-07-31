<?php
require 'vendor/autoload.php'; // Composer로 설치한 라이브러리를 로드합니다.

use Aws\DynamoDb\DynamoDbClient;

// AWS 계정 자격 증명 정보를 설정합니다.
$credentials = new Aws\Credentials\Credentials('AKIA6GY63CLQL4P7VTP4', '6NoqKVzDTY02lZPVp9qMYUhErkIaIUOPLxvjT1Gz');

// 리전 및 자격 증명 정보를 사용하여 DynamoDB 클라이언트를 생성합니다.
$dynamodb = new DynamoDbClient([
    'region' => 'ap-northeast-2',
    'version' => 'latest',
    'credentials' => $credentials
]);

// 테이블 이름과 검색 조건을 지정합니다.
$tableName = 'TEROS_TB';
$response = $dynamodb->scan([
    'TableName' => $tableName,
]);

$items = $response['Items'];

while (isset($response['LastEvaluatedKey'])) {
    $response = $dynamodb->scan([
        'TableName' => $tableName,
        'ExclusiveStartKey' => $response['LastEvaluatedKey'],
    ]);
    $items = array_merge($items, $response['Items']);
}

// 전체 결과를 출력합니다.
foreach ($items as $item) {
    echo '<tr><td>' . $item['ID']['N'] . '</td><td>' . $item['DATE']['S'] . '</td><td>' . $item['soilMoist']['N'] . '</td><td>' . $item['temperature']['N'] . '</td><td>' . $item['state']['S'] . '</td></tr>';
}
?>
