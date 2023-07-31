<!-- aws dynamodb 연결 -->
<!-- aws에서 데이터를 가져올 때 php대신 활용하기 좋은 트렌디한 방식을 잘 모르겠다 -->
<?php
require 'vendor/autoload.php'; 

use Aws\DynamoDb\DynamoDbClient;
use Aws\Exception\AwsException;

$credentials = new Aws\Credentials\Credentials(
    'AKIA6GY63CLQL4P7VTP4',
    '6NoqKVzDTY02lZPVp9qMYUhErkIaIUOPLxvjT1Gz'
);

$region = 'ap-northeast-2';
$options = [
    'version' => 'latest',
    'region' => $region,
    'credentials' => $credentials
];

$dynamoDBClient = new DynamoDbClient($options);

$params = [
    'TableName' => 'TEROS_TB',
    'ProjectionExpression' => 'ID, temperature, soilMoist' 
];

try {
    // scan으로 전체 데이터를 가져오다보니 데이터가 많아졌을 때 효율이 떨어지는 편
    $result = $dynamoDBClient->scan($params);

    $ids = [];
    $temperatures = [];
    $soilMoists = [];

    foreach ($result['Items'] as $item) {
        $ids[] = $item['ID']['N'];
        $temperatures[] = $item['temperature']['N']; 
        $soilMoists[] = $item['soilMoist']['N'];
    }

    $tempData = [
        'labels' => $ids,
        'datasets' => [
            [
                'data' => $temperatures,
                'label' => "Temperature",
                'borderColor' => "#3e95cd",
                'fill' => false
            ]
        ]
    ];

    $soilMoistData = [
      'labels' => $ids,
      'datasets' => [
          [
              'data' => $soilMoists,
              'label' => "SoilMoist",
              'borderColor' => "#3e95cd",
              'fill' => false
          ]
      ]
  ];


    $tempDataJSON = json_encode($tempData);
    $soilMoistDataJSON = json_encode($soilMoistData);

} catch (AwsException $e) {
    echo "Error accessing DynamoDB: " . $e->getMessage();
    return;
}
?>


<!DOCTYPE html>
<html>
<head>
  <title>TEST PAGE</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
  // ajax로 get_data.php 에서 출력하는 데이터 대시보드를 실시간으로 띄움
$(document).ready(function(){
    setInterval(function(){
        $.ajax({
            url: 'get_data.php', 
            type: 'GET',
            dataType: 'html',
            success: function(data){
                $('#data-container').html(data);
            }
        });
});
});


</script>
</head>


<body>

<!-- dashboard -->
<div class="layout">
  <input name="nav" type="radio" class="nav sensor-radio" id="sensor" checked="checked" />
  <div class="page home-page">
    <div class="page-contents">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Date</th>
          <th>SoilMoist</th>
          <th>Temperature</th>
          <th>State</th>
          
        </tr>
      </thead>
      <tbody id="data-container">

      </tbody>
    </table>


    </div>
  </div>
  <label class="nav" for="sensor">
    <span>
      Sensor
    </span>


  </label>


  <!-- graph -->

  <input name="nav" type="radio" class="graph-radio" id="graph" />
  <div class="page about-page">
    <div class="page-contents">

<!-- 그래프들이 생성되는 영역 -->
<canvas id="temperature-chart" width="100dp" height="60dp" margin = "50dp" ></canvas>
<canvas id="soilMoist-chart" width="100dp" height="60dp"></canvas>


<!-- 그래프 요소들 js코드 삽입 -->
<!-- index 파일 안에 넣은 것이 불편하다면 js파일로 빼낼 수 있음 -->
<script>
        var tempData = <?php echo $tempDataJSON; ?>;

        var ctx1 = document.getElementById("temperature-chart").getContext("2d");
        var chart1 = new Chart(ctx1, {
            type: 'line',
            data: tempData,
            options: {
                title: {
                    display: true,
                    text: 'Temperature Data'
                }
            }
        });



        var soilMoistData = <?php echo $soilMoistDataJSON; ?>;

        var ctx2 = document.getElementById("soilMoist-chart").getContext("2d");
        var chart2 = new Chart(ctx2, {
            type: 'line',
            data: soilMoistData,
            options: {
                title: {
                    display: true,
                    text: 'SoilMoist Data'
                }
            }
        });
    </script>


  

    </div>
  </div>
  <label class="nav" for="graph">

    <span>
      Graph
      </span>
    </label>



    <!-- image -->
    <!-- bucket에서 이미지를 가져오는 영역 -->
  <input name="nav" type="radio" class="image-radio" id="image" />
  <div class="page about-page">
    <div class="page-contents">


    <div id="image-container"></div>

  <script>
    // 서버에서 이미지 목록을 가져오는 함수
    async function fetchImages() {
      try {
        const response = await fetch('/images');
        const images = await response.json();
        return images;
      } catch (error) {
        console.error('Error fetching images:', error);
        return [];
      }
    }

    // 이미지를 동적으로 추가하는 함수
    function displayImages(images) {
      const imageContainer = document.getElementById('image-container');
      images.forEach((image) => {
        const imgElement = document.createElement('img');
        imgElement.src = image.url;
        imgElement.alt = image.name;
        imageContainer.appendChild(imgElement);
      });
    }

    // 페이지 로드 시 이미지 목록을 가져와서 띄우기
    window.onload = async () => {
      const images = await fetchImages();
      displayImages(images);
    };
  </script>

    </div>
  </div>
  <label class="nav" for="image">

    <span>
      Image
      </span>
    </label>


  
</div>
 </body>
</html>