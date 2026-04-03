<?php

declare(strict_types=1);

set_time_limit(0);
ini_set('memory_limit', '-1');
mysqli_report(MYSQLI_REPORT_OFF);

function stderr(string $message): void
{
	fwrite(STDERR, $message . PHP_EOL);
}

function stdout(string $message): void
{
	fwrite(STDOUT, $message . PHP_EOL);
}

function parse_cli_args(array $argv): array
{
	$options = array(
		'dump' => null,
		'mode' => 'full',
	);

	foreach (array_slice($argv, 1) as $argument) {
		if (str_starts_with($argument, '--dump=')) {
			$options['dump'] = substr($argument, 7);
			continue;
		}

		if (str_starts_with($argument, '--mode=')) {
			$options['mode'] = substr($argument, 7);
			continue;
		}
	}

	if (!in_array($options['mode'], array('full', 'missing'), true)) {
		stderr("Invalid mode '{$options['mode']}'. Use --mode=full or --mode=missing.");
		exit(1);
	}

	return $options;
}

function parse_wp_config(string $wpConfigPath): array
{
	$contents = file_get_contents($wpConfigPath);

	if ($contents === false) {
		stderr("Unable to read wp-config.php at {$wpConfigPath}");
		exit(1);
	}

	$keys = array('DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST', 'DB_CHARSET');
	$config = array();

	foreach ($keys as $key) {
		$pattern = '/define\(\s*[\'"]' . preg_quote($key, '/') . '[\'"]\s*,\s*[\'"]([^\'"]*)[\'"]\s*\)/';
		if (!preg_match($pattern, $contents, $matches)) {
			stderr("Unable to parse {$key} from {$wpConfigPath}");
			exit(1);
		}

		$config[$key] = $matches[1];
	}

	return $config;
}

function collect_dump_tables(string $dumpPath): array
{
	$tables = array();
	$handle = fopen($dumpPath, 'rb');

	if (!$handle) {
		stderr("Unable to open dump file {$dumpPath}");
		exit(1);
	}

	while (($line = fgets($handle)) !== false) {
		if (preg_match('/^CREATE TABLE `([^`]+)`/', $line, $matches)) {
			$tables[] = $matches[1];
		}
	}

	fclose($handle);

	return array_values(array_unique($tables));
}

function collect_existing_tables(mysqli $mysqli): array
{
	$tables = array();
	$result = $mysqli->query('SHOW TABLES');

	if (!$result) {
		stderr('Unable to list existing tables: ' . $mysqli->error);
		exit(1);
	}

	while ($row = $result->fetch_row()) {
		$tables[] = $row[0];
	}

	return $tables;
}

function import_dump_tables(mysqli $mysqli, string $dumpPath, array $targetTables): array
{
	$targetSet = array_fill_keys($targetTables, true);
	$handle = fopen($dumpPath, 'rb');

	if (!$handle) {
		stderr("Unable to open dump file {$dumpPath}");
		exit(1);
	}

	$currentTable = null;
	$shouldImport = false;
	$statement = '';
	$executed = 0;
	$errors = array();
	$started = array();

	while (($line = fgets($handle)) !== false) {
		if (preg_match('/^-- Table structure for table `([^`]+)`/', $line, $matches)) {
			$currentTable = $matches[1];
			$shouldImport = isset($targetSet[$currentTable]);
			$statement = '';

			if ($shouldImport && !isset($started[$currentTable])) {
				$started[$currentTable] = true;
				stdout("[import] {$currentTable}");
			}

			continue;
		}

		if (!$shouldImport) {
			continue;
		}

		$trimmed = trim($line);

		if ($trimmed === '' || strncmp($trimmed, '--', 2) === 0) {
			continue;
		}

		if (preg_match('/^\/\*![0-9]{5} .* \*\/$/', $trimmed)) {
			continue;
		}

		$statement .= $line;

		if (!str_ends_with(rtrim($trimmed), ';')) {
			continue;
		}

		$sql = trim($statement);
		$statement = '';

		if ($sql === '' || !preg_match('/^(DROP TABLE|CREATE TABLE|INSERT INTO)/i', $sql)) {
			continue;
		}

		if (!$mysqli->query($sql)) {
			$errors[] = array(
				'table' => $currentTable,
				'error' => $mysqli->error,
			);
			stderr("[warn] {$currentTable}: {$mysqli->error}");
			continue;
		}

		$executed++;
	}

	fclose($handle);

	return array(
		'executed' => $executed,
		'errors'   => $errors,
	);
}

$options = parse_cli_args($argv);
$projectRoot = realpath(dirname(__DIR__));
$wpConfigPath = $projectRoot . DIRECTORY_SEPARATOR . 'wp-config.php';
$config = parse_wp_config($wpConfigPath);

$defaultDumpPath = $projectRoot . DIRECTORY_SEPARATOR . 'wp_condo_backup.no-locks.sql';
if (!file_exists($defaultDumpPath)) {
	$defaultDumpPath = $projectRoot . DIRECTORY_SEPARATOR . 'wp_condo_backup.sql';
}

$dumpPath = $options['dump'] ? realpath($options['dump']) : realpath($defaultDumpPath);

if ($dumpPath === false || !file_exists($dumpPath)) {
	stderr('Dump file not found.');
	exit(1);
}

$mysqli = new mysqli(
	$config['DB_HOST'],
	$config['DB_USER'],
	$config['DB_PASSWORD'],
	$config['DB_NAME']
);

if ($mysqli->connect_errno) {
	stderr('Database connection failed: ' . $mysqli->connect_error);
	exit(1);
}

$charset = $config['DB_CHARSET'] ?: 'utf8mb4';
$mysqli->set_charset($charset);
$mysqli->query("SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO'");
$mysqli->query('SET FOREIGN_KEY_CHECKS=0');
$mysqli->query('SET UNIQUE_CHECKS=0');
$mysqli->query('SET SQL_NOTES=0');

$dumpTables = collect_dump_tables($dumpPath);
$existingTables = collect_existing_tables($mysqli);

$targetTables = $options['mode'] === 'missing'
	? array_values(array_diff($dumpTables, $existingTables))
	: $dumpTables;

sort($targetTables);

stdout('[info] dump=' . $dumpPath);
stdout('[info] mode=' . $options['mode']);
stdout('[info] target_tables=' . count($targetTables));

if (!$targetTables) {
	stdout('[done] Nothing to import.');
	exit(0);
}

$result = import_dump_tables($mysqli, $dumpPath, $targetTables);
$finalTables = collect_existing_tables($mysqli);
$remainingTables = array_values(array_diff($dumpTables, $finalTables));
sort($remainingTables);

$mysqli->query('SET SQL_NOTES=1');
$mysqli->query('SET UNIQUE_CHECKS=1');
$mysqli->query('SET FOREIGN_KEY_CHECKS=1');

stdout('[done] executed_statements=' . $result['executed']);
stdout('[done] sql_warnings=' . count($result['errors']));
stdout('[done] remaining_tables=' . count($remainingTables));

if ($result['errors']) {
	$tablesWithWarnings = array_values(array_unique(array_map(
		static fn(array $error): string => $error['table'],
		$result['errors']
	)));
	stdout('[warn] tables_with_warnings=' . implode(', ', $tablesWithWarnings));
}

if ($remainingTables) {
	foreach ($remainingTables as $table) {
		stderr('[missing] ' . $table);
	}
	exit(1);
}

exit(0);
