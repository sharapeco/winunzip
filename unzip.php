<?php
require_once 'zzl2/unicode_combination.php';

$file = $argv[1];
$destDir = preg_replace('/[.].*\\z/', '', basename($file));

$zip = new ZipArchive();
if (!$zip->open($file)) {
	die('Failed to open zip archive.');
}

for ($i = 0; $stat = $zip->statIndex($i); $i++) {
	$name = ltrim($stat['name'], '/\\');
	$name = UnicodeCombination::normalize($name);
	$name = mb_convert_encoding($name, 'sjis-win', 'UTF-8');
	if (strpos($name, '..') !== false) {
		continue;
	}

	// Directory
	if (preg_match('{/\\z}', $name)) {
		$dir = $destDir . DIRECTORY_SEPARATOR . $name;
		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}
		continue;
	}

	$file = $destDir . DIRECTORY_SEPARATOR . $name;
	$dir = dirname($file);
	if (!file_exists($dir)) {
		mkdir($dir, 0755, true);
	}
	echo "Extracting $name\n";
	file_put_contents($file, $zip->getFromIndex($i), LOCK_EX);
}

$zip->close();
