<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests;

use yii\console;
use yii\base;
use yii\db;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    private ?string $migrationNamespace = null;
    private ?string $bootstrapClass = null;

    /**
     * @var db\Migration[]
     */
    private array $migrations = [];

    protected function setUp(): void
    {
        parent::setUp();

        $dsn = getenv('DB_TYPE')
            . ":host=" . getenv("DB_HOST")
            . ";port=" . getenv("DB_PORT")
            . ";dbname=" . getenv("DB_NAME");

        $config = [
            'id' => 'package-test',
            'basePath' => dirname(__DIR__),
            'components' => [
                'db' => [
                    'class' => db\Connection::class,
                    'dsn' => $dsn,
                    'username' => getenv('DB_USER'),
                    'password' => getenv('DB_PASS') ?: '',
                ],
            ],
            'bootstrap' => [
                $this->getBootstrapClass(),
            ],
        ];

        \Yii::$app = new console\Application($config);

        $this->migrations = [];
        // need to transform app package alias to real app alias
        $migrationsDir = str_replace(
            'vendor/' . $this->getPackageName() . '/',
            '',
            \Yii::getAlias('@' . str_replace('\\', '/', $this->getMigrationNamespace()))
        );

        /** @var db\Migration $migration */
        foreach ($this->getMigrations($migrationsDir) as $migration) {
            $this->migrations[] = $migration;

            ob_start();
            if ($migration->up() === false) {
                ob_end_flush();
                throw new \Exception("Migration failed.");
            }
            ob_end_clean();
        }
    }

    private function getMigrations(string $migrationsDir): array
    {
        $migrations = [];
        foreach (new \DirectoryIterator($migrationsDir) as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $migrations[] = $file->getFilename();
        }
        sort($migrations);
        foreach ($migrations as $i => $migrationFilename) {
            $migrationClass = $this->getMigrationNamespace()
                . '\\'
                . str_replace('.php', '', $migrationFilename);
            $migration = new $migrationClass();
            if (!$migration instanceof db\Migration) {
                unset($migrations[$i]);
                continue;
            }
            $migration->db = \Yii::$app->db;
            $migrations[$i] = $migration;
        }
        return $migrations;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        /** @var db\Migration $migration */
        foreach (array_reverse($this->migrations) as $migration) {
            ob_start();
            $migration->down();
            ob_end_clean();
        }
        \Yii::$app = null;
    }

    private function getPackageName(): string
    {
        $composerJsonPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'composer.json';
        $packageInfo = json_decode(
            file_get_contents($composerJsonPath),
            true,
            16,
            JSON_THROW_ON_ERROR
        );
        return $packageInfo['name'];
    }

    private function getMigrationNamespace(): string
    {
        return $this->migrationNamespace
            ?? str_replace('\\Tests', '\\Migrations', __NAMESPACE__);
    }

    private function getBootstrapClass(): string
    {
        return $this->bootstrapClass
            ?? str_replace('\\Tests', '\\Migrations\\Bootstrap', __NAMESPACE__);
    }
}
