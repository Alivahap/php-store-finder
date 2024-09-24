<?php
require_once "searchwithdatabase.php";
?>
<?php

header('Content-Type: application/json');

$start = $_GET['start'];
$end = $_GET['end'];

// Örnek verilerle dönüş yapıyoruz
function findPath($start, $end) {
    $database = new Database();
    $db = $database->getConnection();

    // Grid sınıfı ile verileri veritabanından alıyoruz
    $grid = new Grid($db);
    $pathfinder = new Pathfinder($grid);

   
     foreach ($grid->kiosks as $kioskName => $kioskCoords) {
       
         foreach ($grid->stores as $storeName => $storeCoords) {
           
             if($kioskName==$start && $storeName ==$end ){
                
                 $path = $pathfinder->findPath($kioskCoords, $storeCoords);
                 $points = explode(" -> ", $path);

                 // Her bir noktayı diziye çeviriyoruz
                 $result = array_map(function($point) {
                     // Parantezleri ve boşlukları kaldırıyoruz
                     $cleanedPoint = trim($point, '() ');
                     // Virgülle ayırarak diziyi oluşturuyoruz
                     return explode(',', str_replace(' ', '', $cleanedPoint));
                 }, $points);

                 return $result;
               
             }
          
             
         }
        }
        

   
    
    // Diğer yollar burada eklenebilir...
    return [];
}

$path = findPath($start, $end);

echo json_encode(['path' => $path]);
?>
