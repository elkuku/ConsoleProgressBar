<?php
include '../../../../../vendor/autoload.php';

use Elkuku\Console\Helper\ConsoleProgressBar;

print "This will display a very simple bar:\n";

$bar = new ConsoleProgressBar('%bar%', '=', ' ', 76, 3);

for ($i = 0; $i <= 3; $i++)
{
	$bar->update($i);
	sleep(1);
}

print "\n";
