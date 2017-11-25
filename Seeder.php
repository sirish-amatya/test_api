<?php
//For executing seed in local machine
if (isset($argv[1]) && trim($argv[1]) == 'stage') {
    require __DIR__."/src/config/constants.php";
} else {
    require __DIR__."/constants.php";
}

$servername = DB_HOST;
$username = DB_USER;
$password = DB_PASS;
$dbname = DB_NAME;

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo $e->getMessage();
}

$json = file_get_contents(__DIR__.'/sample-data.json');

$insert_arr = json_decode($json, true);

try {
    $pdo->query('DROP TABLE IF EXISTS students');
    $pdo->query(
        'CREATE TABLE `students` (
        `id` varchar(30),
        `first` varchar(100),
        `last` varchar(100),
        `eyeColor` char(20),
        `age` int (3),
        `isActive` boolean,
        PRIMARY KEY (`id`),
        FULLTEXT KEY `name` (`first`,`last`)
        )'
    );

    $stmt = $pdo->prepare('INSERT INTO students (id, first, last, eyeColor, age, isActive ) VALUES (:id, :first, :last, :eyeColor, :age, :isActive)');


    foreach ($insert_arr as $tmp_arr) {
        $tmp_arr['isActive'] = (!empty($tmp_arr['isActive']))?1:0;
        $stmt->bindParam(':id', $tmp_arr['_id']);
        $stmt->bindParam(':first', $tmp_arr['name']['first']);
        $stmt->bindParam(':last', $tmp_arr['name']['last']);
        $stmt->bindParam(':eyeColor', $tmp_arr['eyeColor']);
        $stmt->bindParam(':age', $tmp_arr['age']);
        $stmt->bindParam(':isActive', $tmp_arr['isActive']);
        $stmt->execute();
    }
    print "Seeder Executed!".PHP_EOL;
} catch (Exception $e) {
    echo $e->getMessage();
    echo PHP_EOL;
}
