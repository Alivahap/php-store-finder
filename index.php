<?php
require_once "searchwithdatabase.php";
?>

<?php $database = new Database();
        $db = $database->getConnection();

        // Grid sınıfı ile verileri veritabanından alıyoruz
        $grid = new Grid($db);
        $pathfinder = new Pathfinder($grid);
?>


<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yol Tarifi Sistemi - Birden Fazla Kiosk ve Mağaza</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
        }

        .matrix-container {
            display: flex;
            justify-content: space-around;
        }

        .matrix {
            display: grid;
            grid-template-columns: repeat(11, 40px);
            grid-template-rows: repeat(11, 40px);
            gap: 2px;
            margin-bottom: 20px;
        }

        .cell {
            width: 40px;
            height: 40px;
            background-color: #eaeaea;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 12px;
            border: 1px solid #ddd;
            position: relative;
        }

        .cell.path {
            background-color: yellow;
        }

        .cell.kiosk {
            background-color: lightblue;
        }

        .cell.store {
            background-color: lightgreen;
        }

        .cell.elevator {
            background-color: orange;
        }

        .cell .label {
            position: absolute;
            bottom: 2px;
            right: 2px;
            font-size: 10px;
        }

        .controls {
            margin-bottom: 20px;
            text-align: center;
        }

        .controls select {
            padding: 5px;
            margin-right: 10px;
        }

        .controls button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        .controls button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Yol Tarifi Sistemi - Birden Fazla Kiosk ve Mağaza</h1>
        
        <div class="controls">
            <label for="kioskSelect">Başlangıç Kiosk:</label>
            <select id="kioskSelect">
    <?php

    $kiosks = [];
 foreach ($grid->kiosks as $kioskName => $kioskCoords) {
           
            echo "<option value=$kioskName>$kioskName</option>";

            $kiosks += (array($kioskName."kiosku" =>['x' => $kioskCoords->x,'y' => $kioskCoords->y,'z' => $kioskCoords->z ])  );
           
        }
       
        ?>
                

            </select>
            
            <label for="storeSelect">Hedef Mağaza:</label>
            <select id="storeSelect">
                <?php
                 $stores = [];
            foreach ($grid->stores as $storeName => $storeCoords) {
           
           echo "<option value=$storeName>$storeName</option>";

           $stores += (array($storeName =>['x' => $storeCoords->x,'y' => $storeCoords->y,'z' => $storeCoords->z ])  );
          
       }
       ?>
                
            </select>
            
            <button onclick="findRoute()">Yolu Bul</button>
        </div>

        <div class="matrix-container">
            <div>
                <h3>1. Kat</h3>
                <div id="matrix1" class="matrix"></div>
            </div>
            <div>
                <h3>2. Kat</h3>
                <div id="matrix2" class="matrix"></div>
            </div>
        </div>
    </div>
<?php 
 
 $kiosksJson = json_encode($kiosks);
 $storesJson = json_encode($stores);
 
 ?>


    <script>
        const matrixSize = 11; // 11x11 matris

         // Matris oluşturucu
         function createMatrix(containerId) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            for (let i = 0; i < matrixSize; i++) {
                for (let j = 0; j < matrixSize; j++) {
                    const cell = document.createElement('div');
                    cell.classList.add('cell');
                    cell.dataset.x = String.fromCharCode(65 + j); // A'dan K'ya
                    cell.dataset.y = i + 1;
                    container.appendChild(cell);
                }
            }
        }

        // 1. ve 2. katı oluştur
        createMatrix('matrix1');
        createMatrix('matrix2');

        // Belirli kiosk ve mağazaları işaretle
        function markSpecialLocations() {

           /* const kiosks = {
                'F1': { x: 'F', y: 1, z: 1 },
                'G2': { x: 'G', y: 2, z: 1 },
                'H3': { x: 'H', y: 3, z: 1 }
            };
            */
            const kiosks = <?php echo $kiosksJson; ?>;
            const stores = <?php echo $storesJson; ?>;
 // Kioskları işaretle
 for (const kiosk in kiosks) {
                const location = kiosks[kiosk];
                const cell = document.querySelector(`#matrix${location.z} .cell[data-x="${location.x}"][data-y="${location.y}"]`);
                if (cell) {
                    cell.classList.add('kiosk');
                    cell.innerHTML = `<div class="label">${kiosk}</div>`;
                }
            }

            // Mağazaları işaretle
            for (const store in stores) {
                const location = stores[store];
                const cell = document.querySelector(`#matrix${location.z} .cell[data-x="${location.x}"][data-y="${location.y}"]`);
                if (cell) {
                    cell.classList.add('store');
                    cell.innerHTML = `<div class="label">${store}</div>`;
                }
            }

            // Asansör veya merdiveni işaretle
           // document.querySelector('#matrix1 .cell[data-x="F"][data-y="5"]').classList.add('elevator'); // F5 asansör
           // document.querySelector('#matrix2 .cell[data-x="E"][data-y="6"]').classList.add('elevator'); // E6 asansör
        }
        markSpecialLocations();

        // PHP'deki findPath() metodu ile dönen yolu çiz
        function findRoute() {
            const start = document.getElementById('kioskSelect').value;
            const end = document.getElementById('storeSelect').value;

            // PHP'den dönmesini beklediğimiz örnek yol formatı
            fetch('find_path.php?start=' + start + '&end=' + end)
                .then(response => response.json())
                .then(data => {
                    const path = data.path;
                    drawPath(path);
                });
        }

        // Rota çizme
        function drawPath(path) {
            // Önce eski yolu temizle
            document.querySelectorAll('.cell.path').forEach(cell => {
                cell.classList.remove('path');
            });

            // Yeni yolu çiz
            path.forEach(step => {
                const [x, y, z] = step;
                const cell = document.querySelector(`#matrix${z} .cell[data-x="${x}"][data-y="${y}"]`);
                if (cell) {
                    cell.classList.add('path');
                }
            });
        }
    </script>
</body>
</html>