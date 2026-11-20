<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('PetAdminLab5');
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'path' => '/']);
    session_start();
}
$configFile = __DIR__ . '/config.local.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Configuration missing. Copy config.example.php to config.local.php and enter your local database settings.');
}
$cfg = require $configFile;
try {
    $pdo = new PDO(
        'mysql:host=' . $cfg['host'] . ';port=' . $cfg['port'] . ';dbname=' . $cfg['database'] . ';charset=utf8mb4',
        $cfg['username'], $cfg['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Cannot connect to the database. Check that XAMPP MySQL is running and config.local.php is correct.');
}
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));

$entities = [
    'Pets' => [
        'page' => 'pets.php', 'keys' => ['PetID'],
        'fields' => ['Name'=>'text!', 'Age'=>'number!', 'Street'=>'text', 'City'=>'text', 'ZipCode'=>'text', 'State'=>'text', 'TypeofPet'=>'text!']
    ],
    'Owners' => [
        'page' => 'owners.php', 'keys' => ['OID'],
        'fields' => ['LastName'=>'text!', 'Street'=>'text', 'City'=>'text', 'ZipCode'=>'text', 'State'=>'text', 'Age'=>'number', 'AnnualIncome'=>'money']
    ],
    'Owns' => [
        'page' => 'owns.php', 'keys' => ['PetID','OID'],
        'fields' => ['PetID'=>'pet!', 'OID'=>'owner!', 'Year'=>'year!', 'PetAgeatOwnership'=>'number', 'PricePaid'=>'money']
    ],
    'Foods' => [
        'page' => 'foods.php', 'keys' => ['FoodID'],
        'fields' => ['Name'=>'text!', 'Brand'=>'text!', 'TypeofFood'=>'text!', 'Price'=>'money!', 'ItemWeight'=>'money', 'ClassofFood'=>'text']
    ],
    'Purchases' => [
        'page' => 'purchases.php', 'keys' => ['OID','FoodID','PetID','Month','Year'],
        'fields' => ['OID'=>'owner!', 'FoodID'=>'food!', 'PetID'=>'pet!', 'Month'=>'month!', 'Year'=>'year!', 'Quantity'=>'number!']
    ]
];
function h($value) { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function encode_key($row, $keys) {
    $map = [];
    foreach ($keys as $key) $map[$key] = (string)$row[$key];
    return rtrim(strtr(base64_encode(json_encode($map)), '+/', '-_'), '=');
}
function decode_key($encoded, $keys) {
    $raw = base64_decode(strtr((string)$encoded, '-_', '+/'), true);
    $obj = $raw === false ? null : json_decode($raw, true);
    if (!is_array($obj) || array_keys($obj) !== $keys) throw new RuntimeException('Invalid record identifier.');
    foreach ($keys as $k) if (!is_scalar($obj[$k])) throw new RuntimeException('Invalid identifier.');
    return $obj;
}
function where_key($keys, $values, &$args) {
    $parts = [];
    foreach ($keys as $k) { $parts[] = "`$k` = ?"; $args[] = $values[$k]; }
    return implode(' AND ', $parts);
}
function options_for(PDO $pdo, $type) {
    $queries = [
        'pet' => 'SELECT PetID AS id, CONCAT(PetID, " - ", Name) AS label FROM Pets ORDER BY PetID',
        'owner' => 'SELECT OID AS id, CONCAT(OID, " - ", LastName) AS label FROM Owners ORDER BY OID',
        'food' => 'SELECT FoodID AS id, CONCAT(FoodID, " - ", Name) AS label FROM Foods ORDER BY FoodID'
    ];
    return $pdo->query($queries[$type])->fetchAll();
}
function cleaned_values($fields, $raw) {
    $result = [];
    foreach ($fields as $name => $spec) {
        $required = str_ends_with($spec, '!');
        $type = rtrim($spec, '!');
        $value = trim((string)($raw[$name] ?? ''));
        if ($value === '') {
            if ($required) throw new RuntimeException("$name is required.");
            $result[$name] = null;
            continue;
        }
        if (in_array($type, ['number','year','month','pet','owner','food'], true)) {
            $n = filter_var($value, FILTER_VALIDATE_INT);
            if ($n === false || $n < 0 || ($type === 'month' && ($n < 1 || $n > 12)) || ($type === 'year' && ($n < 1 || $n > 9999)) || (in_array($type, ['pet','owner','food'], true) && $n === 0) || ($name === 'Quantity' && $n === 0)) {
                throw new RuntimeException("$name must be a valid whole number.");
            }
            $value = $n;
        } elseif ($type === 'money') {
            if (!is_numeric($value) || (float)$value < 0 || !preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
                throw new RuntimeException("$name must be a nonnegative amount with at most two decimal places.");
            }
        } elseif (strlen($value) > (in_array($name, ['ZipCode','State'], true) ? 50 : 255)) {
            throw new RuntimeException("$name is too long.");
        }
        $result[$name] = $value;
    }
    return $result;
}
function show_page($table) {
    global $pdo, $entities;
    if (!isset($entities[$table])) { http_response_code(404); exit; }
    $definition = $entities[$table];
    $fields = $definition['fields'];
    $keys = $definition['keys'];
    $page = $definition['page'];
    $error = '';
    $notice = $_SESSION['notice'] ?? '';
    $trace = $_SESSION['last_pets_trace'] ?? null;
    unset($_SESSION['notice'], $_SESSION['last_pets_trace']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) throw new RuntimeException('Form expired. Reload this page.');
            $operation = $_POST['operation'] ?? '';
            if ($operation === 'add' || $operation === 'update') {
                $values = cleaned_values($fields, $_POST);
                $columns = array_keys($values);
                if ($operation === 'add') {
                    $sql = 'INSERT INTO `' . $table . '` (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', array_fill(0, count($columns), '?')) . ')';
                    $pdo->prepare($sql)->execute(array_values($values));
                } else {
                    $old = decode_key($_POST['record'] ?? '', $keys);
                    $args = array_values($values);
                    $condition = where_key($keys, $old, $args);
                    $sets = array_map(fn($k) => "`$k` = ?", $columns);
                    $pdo->prepare('UPDATE `' . $table . '` SET ' . implode(',', $sets) . ' WHERE ' . $condition)->execute($args);
                }
                $_SESSION['notice'] = $operation === 'add' ? 'Record added successfully.' : 'Record updated successfully.';
                if ($table === 'Pets') {
                    $petId = $operation === 'add' ? $pdo->lastInsertId() : ($old['PetID'] ?? '');
                    $_SESSION['last_pets_trace'] = [
                        'request' => 'POST /pets.php — form values received by PHP',
                        'validation' => 'Passed: required fields and nonnegative integer age checked on the server',
                        'database' => strtoupper($operation) . ' completed in MySQL (PetID ' . $petId . ')',
                        'response' => 'HTTP 303 redirect to GET /pets.php; updated table loaded from MySQL'
                    ];
                }
            } elseif ($operation === 'delete') {
                $old = decode_key($_POST['record'] ?? '', $keys);
                $args = [];
                $condition = where_key($keys, $old, $args);
                $pdo->prepare('DELETE FROM `' . $table . '` WHERE ' . $condition)->execute($args);
                $_SESSION['notice'] = 'Record deleted.';
            } else { throw new RuntimeException('Unknown action.'); }
            header('Location: ' . $page, true, 303);
            exit;
        } catch (RuntimeException $e) {
            http_response_code(422);
            $error = $e->getMessage();
            if ($table === 'Pets') $trace = [
                'request' => 'POST /pets.php — form values received by PHP',
                'validation' => 'Rejected: ' . $error,
                'database' => 'No INSERT or UPDATE executed',
                'response' => 'HTTP 422 — form redisplayed with an error message'
            ];
        } catch (PDOException $e) {
            http_response_code(422);
            $error = 'Database rejected the operation. Check duplicate keys, foreign-key relationships and field values.';
        }
    }
    $editRow = null;
    if (isset($_GET['edit'])) {
        try {
            $old = decode_key($_GET['edit'], $keys);
            $args = [];
            $condition = where_key($keys, $old, $args);
            $stmt = $pdo->prepare('SELECT * FROM `' . $table . '` WHERE ' . $condition);
            $stmt->execute($args);
            $editRow = $stmt->fetch() ?: null;
        } catch (RuntimeException $e) { $error = $e->getMessage(); }
    }
    $rows = $pdo->query('SELECT * FROM `' . $table . '` ORDER BY ' . implode(', ', array_map(fn($k) => "`$k`", $keys)))->fetchAll();
    $count = count($rows);
    $tables = array_keys($entities);
    include __DIR__ . '/view.php';
}
