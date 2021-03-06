<?php

use Brick\Geo\Doctrine\Types;
use Brick\Geo\Engine\GeometryEngine;
use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Engine\PDOEngine;
use Brick\Geo\Engine\SQLite3Engine;
use Brick\Geo\Engine\GEOSEngine;
use Doctrine\DBAL\Types\Type;

/** @var \Composer\Autoload\ClassLoader $classLoader */
$classLoader = require 'vendor/autoload.php';

//Add namespace for doctrine base tests
$classLoader->addPsr4('Doctrine\\Tests\\', [
    __DIR__ . '/vendor/doctrine/orm/tests/Doctrine/Tests',
    __DIR__ . '/vendor/doctrine/dbal/tests/Doctrine/Tests'
]);
$classLoader->loadClass('Doctrine\Tests\DbalFunctionalTestCase');
$classLoader->loadClass('Doctrine\Tests\DBAL\Mocks\MockPlatform');

Type::addType('geometry', Types\GeometryType::class);
Type::addType('linestring', Types\LineStringType::class);
Type::addType('multilinestring', Types\MultiLineStringType::class);
Type::addType('multipoint', Types\MultiPointType::class);
Type::addType('multipolygon', Types\MultiPolygonType::class);
Type::addType('point', Types\PointType::class);
Type::addType('polygon', Types\PolygonType::class);


/**
 * @return GeometryEngine
 */
function createGeometryEngine()
{
    switch ($engine = getenv('ENGINE')) {
        case 'PDO_MYSQL':
            $pdo = new PDO('mysql:host=localhost', 'root', '');

            $pdo->exec('DROP DATABASE IF EXISTS geo_tests');
            $pdo->exec('DROP DATABASE IF EXISTS geo_tests_tmp');
            $pdo->exec('CREATE DATABASE geo_tests');
            $pdo->exec('CREATE DATABASE geo_tests_tmp');
            $pdo->exec('USE geo_tests');

            $statement = $pdo->query("SHOW VARIABLES LIKE 'version'");
            $version = $statement->fetchColumn(1);

            echo 'MySQL version: ' . $version . PHP_EOL;

            //Connect data for doctrine integration tests
            $GLOBALS['db_type'] = 'pdo_mysql';
            $GLOBALS['db_host'] = 'localhost';
            $GLOBALS['db_port'] = 3306;
            $GLOBALS['db_username'] = 'root';
            $GLOBALS['db_password'] = '';
            $GLOBALS['db_name'] = 'geo_tests';

            $GLOBALS['tmpdb_type'] = 'pdo_mysql';
            $GLOBALS['tmpdb_host'] = 'localhost';
            $GLOBALS['tmpdb_port'] = 3306;
            $GLOBALS['tmpdb_username'] = 'root';
            $GLOBALS['tmpdb_password'] = '';
            $GLOBALS['tmpdb_name'] = 'geo_tests_tmp';

            $engine = new PDOEngine($pdo);
            break;

        case 'PDO_PGSQL':
            $pdo = new PDO('pgsql:host=localhost', 'postgres', '');

            $pdo->exec('CREATE EXTENSION IF NOT EXISTS postgis;');
            $pdo->exec('DROP DATABASE IF EXISTS geo_tests');
            $pdo->exec('DROP DATABASE IF EXISTS geo_tests_tmp');
            $pdo->exec('CREATE DATABASE geo_tests');
            $pdo->exec('CREATE DATABASE geo_tests_tmp');

            $statement = $pdo->query('SELECT version()');
            $version = $statement->fetchColumn(0);

            echo 'PostgreSQL version: ' . $version . PHP_EOL;

            //Connect data for doctrine integration tests
            $GLOBALS['db_type'] = 'pdo_pgsql';
            $GLOBALS['db_host'] = 'localhost';
            $GLOBALS['db_port'] = 5432;
            $GLOBALS['db_username'] = 'postgres';
            $GLOBALS['db_password'] = 'postgres';
            $GLOBALS['db_name'] = 'geo_tests';

            $GLOBALS['tmpdb_type'] = 'pdo_pgsql';
            $GLOBALS['tmpdb_host'] = 'localhost';
            $GLOBALS['tmpdb_port'] = 5432;
            $GLOBALS['tmpdb_username'] = 'postgres';
            $GLOBALS['tmpdb_password'] = 'postgres';
            $GLOBALS['tmpdb_name'] = 'geo_tests_tmp';

            $engine = new PDOEngine($pdo);
            break;

        case 'SQLite3':
            $sqlite3 = new SQLite3(':memory:');
            $prefix = '';
            if (getenv('TRAVIS_PHP_VERSION') === 'hhvm') {
                $prefix = '/usr/local/lib/';
            }

            $sqlite3->loadExtension($prefix . 'libspatialite.so');

            $statement = $sqlite3->query('SELECT sqlite_version(), spatialite_version()');
            list ($sqliteVersion, $spatialiteVersion) = $statement->fetchArray(SQLITE3_NUM);

            echo 'SQLite version: ' . $sqliteVersion . PHP_EOL;
            echo 'SpatiaLite version: ' . $spatialiteVersion . PHP_EOL;

            $engine = new SQLite3Engine($sqlite3);
            break;

        case 'GEOS':
            echo 'GEOS version: ' . GEOSVersion();

            $engine = new GEOSEngine();
            break;

        default:
            if ($engine === false) {
                echo 'ENGINE environment variable not set!' . PHP_EOL;
            } else {
                echo 'Unknown engine: ' . $engine . PHP_EOL;
            }

            echo 'Example usage: ENGINE={engine} vendor/bin/phpunit' . PHP_EOL;
            echo 'Available engines: PDO_MYSQL, PDO_PGSQL, SQLite3, GEOS' . PHP_EOL;
            exit(1);
    }

    echo 'Using ', get_class($engine), PHP_EOL;

    return $engine;
}

GeometryEngineRegistry::set(createGeometryEngine());
