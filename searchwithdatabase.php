<?php

// Veritabanı bağlantısı sınıfı
class Database {
    private $host = "localhost"; // Veritabanı sunucusu
    private $db_name = "storeFinder"; // Veritabanı adı
    private $username = "root"; // Veritabanı kullanıcı adı
    private $password = ""; // Veritabanı şifresi
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Veritabanı bağlantı hatası: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// Koordinatları temsil eden sınıf
class Point {
    public $x;
    public $y;
    public $z;

    public function __construct($x, $y, $z = 1) {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z; // Kat bilgisi (default: 1. kat)
    }

    // İki noktanın eşit olup olmadığını kontrol et
    public function equals(Point $point) {
        return $this->x == $point->x && $this->y == $point->y && $this->z == $point->z;
    }
}

// Grid sınıfı, veritabanından verileri yükler ve bloklu alanları kontrol eder
class Grid {
    public $kiosks;
    public $stores;
    public $blockedPoints;
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->kiosks = $this->loadKiosks();
        $this->stores = $this->loadStores();
        $this->blockedPoints = $this->loadBlockedPoints();
    }

    // Kiosk verilerini veritabanından çek
    private function loadKiosks() {
        $kiosks = [];
        $query = "SELECT name, x, y, z FROM kiosks";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $kiosks[$row['name']] = new Point($row['x'], $row['y'], $row['z']);
        }
        return $kiosks;
    }

    // Mağaza verilerini veritabanından çek
    private function loadStores() {
        $stores = [];
        $query = "SELECT name, x, y, z FROM stores";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stores[$row['name']] = new Point($row['x'], $row['y'], $row['z']);
        }
        return $stores;
    }

    // Kör nokta verilerini veritabanından çek
    private function loadBlockedPoints() {
        $blockedPoints = [];
        $query = "SELECT x, y, z FROM blocked_points";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $blockedPoints[] = new Point($row['x'], $row['y'], $row['z']);
        }
        return $blockedPoints;
    }

    // Kör nokta olup olmadığını kontrol et
    public function isBlocked(Point $point) {
        foreach ($this->blockedPoints as $blocked) {
            if ($blocked->equals($point)) {
                return true;
            }
        }
        return false;
    }
}

// Yol bulma sınıfı (A* algoritması)
class Pathfinder {
    private $grid;
    private $stairs;
    private $elevator;

    public function __construct(Grid $grid) {
        $this->grid = $grid;
        // Asansör ve merdiven yerleri
        $this->stairs = new Point('A', 5, 1);
        $this->elevator = new Point('E', 5, 1);
    }

    // Manhattan mesafesi (A* algoritmasının heuristic fonksiyonu)
    private function manhattanDistance(Point $start, Point $end) {
        return abs(ord($start->x) - ord($end->x)) + abs($start->y - $end->y) + abs($start->z - $end->z);
    }

    // Geçerli komşuları bulma
    private function getNeighbors(Point $current) {
        $neighbors = [];
        $directions = [
            ['x' => 0, 'y' => 1],  // Yukarı
            ['x' => 0, 'y' => -1], // Aşağı
            ['x' => 1, 'y' => 0],  // Sağ
            ['x' => -1, 'y' => 0]  // Sol
        ];

        foreach ($directions as $dir) {
            $neighborX = chr(ord($current->x) + ($dir['x'] ?? 0));
            $neighborY = $current->y + ($dir['y'] ?? 0);
            $neighborZ = $current->z;

            $neighbor = new Point($neighborX, $neighborY, $neighborZ);

            // Komşunun geçerli ve engellenmemiş olup olmadığını kontrol et
            if ($neighborX >= 'A' && $neighborX <= 'K' && $neighborY > 0 && $neighborY <= 12
                && ($neighborZ == 1 || $neighborZ == 2) && !$this->grid->isBlocked($neighbor)) {
                $neighbors[] = $neighbor;
            }
        }

        // Eğer şu anki pozisyon asansör veya merdiven ise, kata çıkma inme seçeneği ekle
        if ($current->equals($this->elevator) || $current->equals($this->stairs)) {
            $neighborZ = $current->z == 1 ? 2 : 1; // Kat değiştir
            $neighbors[] = new Point($current->x, $current->y, $neighborZ);
        }

        return $neighbors;
    }

    // A* algoritması ile yol bulma
    public function findPath(Point $start, Point $end) {
        $openSet = [$start];
        $cameFrom = [];
        $gScore = [$this->hashPoint($start) => 0];
        $fScore = [$this->hashPoint($start) => $this->manhattanDistance($start, $end)];

        while (!empty($openSet)) {
            // En düşük fScore'a sahip noktayı seç
            usort($openSet, function($a, $b) use ($fScore) {
                return $fScore[$this->hashPoint($a)] <=> $fScore[$this->hashPoint($b)];
            });
            $current = array_shift($openSet);

            // Hedefe ulaşıldı mı?
            if ($current->equals($end)) {
                return $this->reconstructPath($cameFrom, $current);
            }

            // Komşuları keşfet
            foreach ($this->getNeighbors($current) as $neighbor) {
                $tentativeGScore = $gScore[$this->hashPoint($current)] + 1; // Mesafe sabit 1

                if (!isset($gScore[$this->hashPoint($neighbor)]) || $tentativeGScore < $gScore[$this->hashPoint($neighbor)]) {
                    $cameFrom[$this->hashPoint($neighbor)] = $current;
                    $gScore[$this->hashPoint($neighbor)] = $tentativeGScore;
                    $fScore[$this->hashPoint($neighbor)] = $gScore[$this->hashPoint($neighbor)] + $this->manhattanDistance($neighbor, $end);

                    if (!in_array($neighbor, $openSet)) {
                        $openSet[] = $neighbor;
                    }
                }
            }
        }

        return "Yol bulunamadı.";
    }

    // Hash fonksiyonu (noktaları kolayca karşılaştırmak için)
    private function hashPoint(Point $point) {
        return $point->x . $point->y . $point->z;
    }

    // Bulunan yolu oluşturma
    private function reconstructPath($cameFrom, Point $current) {
        $totalPath = [$current];
        while (isset($cameFrom[$this->hashPoint($current)])) {
            $current = $cameFrom[$this->hashPoint($current)];
            $totalPath[] = $current;
        }

        $pathString = "";
        foreach (array_reverse($totalPath) as $point) {
            if ($point->equals($this->elevator)) {
                $pathString .= "Asansöre binin, ";
            } elseif ($point->equals($this->stairs)) {
                $pathString .= "Merdivene binin, ";
            } else {
                $pathString .= "({$point->x},{$point->y}, {$point->z}) -> ";
            }
        }

        return rtrim($pathString, " -> ");
    }
}




?>
