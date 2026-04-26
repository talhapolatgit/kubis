<?php

declare(strict_types=1);

function loadEnv(string $path): array
{
    $env = [];
    $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $value = trim($value, "\"'");
        $env[$key] = $value;
    }

    return $env;
}

function studly(string $value): string
{
    $value = preg_replace('/[^a-z0-9]+/i', ' ', $value) ?? $value;
    $value = ucwords(strtolower(trim($value)));
    return str_replace(' ', '', $value);
}

function topoSort(array $tables, array $deps): array
{
    $inDegree = array_fill_keys($tables, 0);
    $graph = array_fill_keys($tables, []);

    foreach ($deps as $table => $references) {
        foreach ($references as $ref) {
            if (! isset($inDegree[$ref]) || $ref === $table) {
                continue;
            }
            $graph[$ref][] = $table;
            $inDegree[$table]++;
        }
    }

    $queue = [];
    foreach ($inDegree as $table => $count) {
        if ($count === 0) {
            $queue[] = $table;
        }
    }

    sort($queue);
    $result = [];

    while ($queue !== []) {
        $current = array_shift($queue);
        $result[] = $current;

        foreach ($graph[$current] as $neighbor) {
            $inDegree[$neighbor]--;
            if ($inDegree[$neighbor] === 0) {
                $queue[] = $neighbor;
            }
        }

        sort($queue);
    }

    if (count($result) !== count($tables)) {
        sort($tables);
        return $tables;
    }

    return $result;
}

$root = dirname(__DIR__);
$env = loadEnv($root . DIRECTORY_SEPARATOR . '.env');

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = (int) ($env['DB_PORT'] ?? 3306);
$database = $env['DB_DATABASE'] ?? '';
$username = $env['DB_USERNAME'] ?? '';
$password = $env['DB_PASSWORD'] ?? '';

if ($database === '') {
    fwrite(STDERR, "DB_DATABASE is missing in .env\n");
    exit(1);
}

$mysqli = @new mysqli($host, $username, $password, $database, $port);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "MySQL connection failed: {$mysqli->connect_error}\n");
    exit(1);
}

$migrationsDir = $root . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
if (! is_dir($migrationsDir)) {
    fwrite(STDERR, "Migrations directory not found: {$migrationsDir}\n");
    exit(1);
}

$existing = glob($migrationsDir . DIRECTORY_SEPARATOR . '*.php') ?: [];
foreach ($existing as $file) {
    @unlink($file);
}

$tables = [];
$tableResult = $mysqli->query('SHOW TABLES');
while ($row = $tableResult->fetch_array(MYSQLI_NUM)) {
    $table = $row[0];
    if ($table === 'migrations') {
        continue;
    }
    $tables[] = $table;
}
sort($tables);

$createStatements = [];
$dependencies = array_fill_keys($tables, []);

foreach ($tables as $table) {
    $safeTable = str_replace('`', '``', $table);
    $result = $mysqli->query("SHOW CREATE TABLE `{$safeTable}`");
    $row = $result->fetch_assoc();
    $createSql = $row['Create Table'] ?? '';
    $createStatements[$table] = $createSql;

    preg_match_all('/REFERENCES `([^`]+)`/i', $createSql, $matches);
    $references = $matches[1] ?? [];
    $dependencies[$table] = array_values(array_unique($references));
}

$orderedTables = topoSort($tables, $dependencies);

$baseTime = strtotime('2026-04-26 15:35:00');
$counter = 0;

foreach ($orderedTables as $table) {
    $timestamp = date('Y_m_d_His', $baseTime + $counter);
    $counter++;

    $fileName = "{$timestamp}_create_{$table}_table.php";
    $className = 'Create' . studly($table) . 'Table';
    $createSql = $createStatements[$table];

    $content = <<<PHP
<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
{$createSql}
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('{$table}');
    }
};
PHP;

    file_put_contents($migrationsDir . DIRECTORY_SEPARATOR . $fileName, $content);
}

echo 'Generated ' . count($orderedTables) . " migration files.\n";
