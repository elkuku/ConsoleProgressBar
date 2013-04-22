<?php
include '../../../../../vendor/autoload.php';

use Elkuku\Console\Helper\ConsoleProgressBar;

print "Showing an estimate for the remaining time:\n";

$bar = new ConsoleProgressBar('- %fraction% [%bar%] %percent% ETA: %estimate%', '=>', '-', 78, 345);

for ($i = 0; $i <= 345; $i++)
{
	$bar->update($i);
	usleep(40000);
}

print "\n";
