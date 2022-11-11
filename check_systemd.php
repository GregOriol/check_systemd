<?php

require_once(__DIR__.'/vendor/autoload.php');

require_once(__DIR__.'/inc/nagios.php');

// Command line parser
$parser = new Console_CommandLine(array(
	'description'	=> 'Check systemd failed',
	'version'		=> '0.0.1',
	'force_posix'	=> true
));

// $parser->addOption('state', array(
// 	// 'short_name'	=> '-u',
// 	'long_name'		=> '--state',
// 	'description'	=> 'State of the service',
// 	'action'		=> 'StoreString'
// ));

try {
	$result = $parser->parse();

	// $state = $result->options['state'];
	// if (empty($state)) {
		$state = 'failed';
	// }

	exec('systemctl list-units --state='.$state.' --full --plain --no-legend --no-pager', $output, $return);
	if ($return !== 0) {
		echo 'Couldn\'t retrieve output from systemctl: returned '.$return."\n";
		exit(NAGIOS_UNKNOWN);
	}

	if (empty($output)) {
		echo 'OK - No units in '.$state.' state'."\n";
		exit(NAGIOS_OK);
	}

	$units = [];
	foreach ($output as $row) {
		$r = preg_match('/^([^.]+\.[^ ]+) +([^ ]+) +([^ ]+) +([^ ]+) +(.+)$/', $row, $matches);
		if (!$r || count($matches) !== 6) {
			echo 'Couldn\'t parse output'."\n";
			exit(NAGIOS_UNKNOWN);
		}

		$unit = $matches[1];
		$load = $matches[2];
		$active = $matches[3];
		$sub = $matches[4];
		$description = $matches[5];

		$units[] = $unit;
	}

	$c = count($units);
	if ($c === 1) {
		echo 'CRITICAL - '.count($units).' unit in '.$state.' state: '.join(', ', $units)."\n";
	} else {
		echo 'CRITICAL - '.count($units).' units in '.$state.' state: '.join(', ', $units)."\n";
	}
	exit(NAGIOS_CRITICAL);

} catch (Exception $exc) {
	$parser->displayError($exc->getMessage());
	exit(NAGIOS_UNKNOWN);
}
