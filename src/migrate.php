<?php

/**
 * Миграции хранятся в папке migrations в виде sql-файлов, пронумерованы попорядку
 * порядковый номер sql-файла равен порядковому номеру миграции в таблице в БД
 * В одном файле может содеражаться несколько sql запросов.
 *
 * запуск - набрать в консоли: composer migrate
 * @author O.Dolotova
 */
require('./vendor/autoload.php');

use Kladovochka\MyPDO;

$db = new MyPDO();
$db->query('SET NAMES utf8');

//получаем список файлов в папке migrations
//array(2) {
// [0]=>
// string(53) "/home/ubuntu/kladovochka/kladovochka/migrations/1.sql"
//  [1]=>
//  string(53) "/home/ubuntu/kladovochka/kladovochka/migrations/2.sql"
$fails = glob(__DIR__ . '/migrations/*.sql');

//необходимо проверить правильно ли пронумерованны файлы с миграциями
$migrationsList = [];

foreach ($fails as $m_ => $value) {
    $string = substr(strrchr($value, '/'), 1);
    // в стринг попадает файл - "1.sql", забераем у него порядковый номер
    $numberMigration = explode('.', $string);
    $migrationsList[] = (int)$numberMigration[0];
}
sort($migrationsList);
$i = 0;
foreach ($migrationsList as $key => $value) {
    if ($value - $i != 1) {
        echo "миграция не выполнена,потому что файлы в папке migrations, пронумерованы не попорядку \n";
        exit();
    };
    $i++;
}


//переходим к выполнению миграций, для начала считаем их
$countMigrations = count(glob(__DIR__ . '/migrations/*.sql'));

//проверяем есть ли уже таблица миграций в БД, если нет то создаем
$query = $db->query("SHOW TABLES FROM kladovochka LIKE 'migrations' ");

if (!$query->fetch()) {
    $db->query("
                  CREATE TABLE `migrations` (
                  `id` INT NOT NULL AUTO_INCREMENT,
                  `number` INT NULL,
                  `data` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
                  UNIQUE INDEX `number_UNIQUE` (`number` ASC),
                  PRIMARY KEY (`id`))
                  ");
}

//ищем последнюю миграцию в таблице
$sql = "SELECT number FROM migrations ORDER BY number DESC LIMIT 1";

$lastMigrationInDB = $db->query($sql)->fetch();


//если таблица с миграциями пустая, то есть возвращается false - присваиваем значение 0
if ($lastMigrationInDB === false) {
    $lastMigrationInDB['number'] = 0;
}

if ((int)$lastMigrationInDB['number'] == $countMigrations) {
    echo "nothing to migrate\n";
    exit();
}

for ($i = (int)$lastMigrationInDB['number'] + 1; $i <= $countMigrations; $i++) {

    //получаем запрос из файла с миграцией и выполняем его
    $migrationQuery = file_get_contents(__DIR__ . '/migrations/' . $i . '.sql');

    //если файл с миграцие существует выполняем запрос
    if ($migrationQuery) {
        try {
            $db->beginTransaction();
            $db->query($migrationQuery);
            $sql = "INSERT INTO `migrations` (`number`) VALUES ('{$i}')";
            //добавляем миграцию в таблицу с миграциями в БД
            $db->query($sql);
            echo "добавлена миграция №" . $i . "\n";
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw new Exception(
                "миграция migrations/{$i}.sql не выполнена, исправьте ошибку и заново запустите скрипт",
                0,
                $e
            );
        }
    } else {
        echo "не удается открыть файл" . $i . ".sql \n";
    }
}
