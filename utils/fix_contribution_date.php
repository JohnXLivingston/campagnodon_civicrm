#!/usr/bin/php
<?php

if (PHP_SAPI !== 'cli') {
  die("Can only be called in CLI mode");
}

// To initialise the CiviCRM env, we have to eval `cv php:boot`.
// This is the official way to do, see https://docs.civicrm.org/dev/en/latest/framework/bootstrap/#independent-scripts
eval(`cv php:boot`);

function print_help() {
  echo "This script is using the 'cv' tool.\n";
  echo "Be sure to have it installed, and to be in a directory where cv can find your CiviCRM installation.\n";
  echo "\n";
  echo "To use this script, call:\n";
  echo "  cat dates.json | php /the_path_to_campagnodon/utils/fix_contribution_date.php run\n";
  echo "or\n";
  echo "  cat dates.json | php /the_path_to_campagnodon/utils/fix_contribution_date.php test\n";
  echo "The dates.json file contains the transaction idx to modify, with the dates to use for contributions.\n";
  echo "Here is an example:\n";
  echo "  [{idx:'campagnodon/00001228', date:'2023-01-01 12:32:00'}]\n";

  echo "It will check for each listed transaction the 'contribution_date' value, ";
  echo "and for each associated contributions the receive_date value. ";
  echo "If the date is not the correct one (will only check the date, not the hour), ";
  echo "it will change the dates using CiviCRM APIv4.\n";

  echo "\nThe first argument must be one of:\n";
  echo "- 'test': will only read data and print what it would have be done. Don't modify any data.\n";
  echo "- 'run': will do the fixes.\n";
}

function die_with_help($message) {
  echo 'ERROR: ';
  echo $message;
  echo "\n\n";
  print_help();
  die();
}

echo "Executing fix_contribution_date.php script...\n";
echo "Testing if CiviCRM env is set...\n";
use CRM_CampagnodonCivicrm_ExtensionUtil as E;
try {
  if (empty(E::path())) {
    die_with_help('Missing CRM_CampagnodonCivicrm_ExtensionUtil::path(), it seems that the CiviCRM env is not correctly set.');
  }
} catch (Throwable $e) {
  echo "ERROR: ".$e."\n";
  die_with_help('It seems that the CiviCRM env is not correctly set.');
}

echo "Testing arguments...\n";
if ($argc !== 2) {
  die_with_help('Wrong arguments number');
}

$test_mode = true;
if ($argv[1] === 'run') {
  $test_mode = false;
} elseif ($argv[1] !== 'test') {
  die_with_help('Wrong arguments');
}

echo "Reading standard input...\n";
function read_input() {
  $input = '';
  $f = fopen('php://stdin', 'r');
  while( $line = fgets($f) ) {
    $input.= $line."\n";
  }
  fclose($f);
  $json = json_decode($input, false);
  return $json;
}
$json = read_input();
if (empty($json)) {
  die_with_help('Input empty or invalid.');
}

if (!is_array($json)) {
  die_with_help('JSON input is not an array.');
}

function check_json($json) {
  foreach ($json as $line) {
    if (!is_object($line)) {
      die_with_help('Invalid JSON, some lines are not objects.');
    }
    if (!property_exists($line, 'idx')) {
      die_with_help('Invalid JSON, some lines have no idx attribute.');
    }
    if (!property_exists($line, 'contribution_date')) {
      die_with_help('Invalid JSON, some lines have no contribution_date attribute.');
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $line->contribution_date)) {
      die_with_help('Invalid JSON, some lines invalid contribution_date: "'.$line->contribution_date.'".');
    }
  }
}
check_json($json);

echo "Fixing...\n\n";
function do_fix($test_mode, $json) {
  foreach ($json as $line) {
    $idx = $line->idx;
    echo "Transaction: ".$idx."\n";
    $wanted_contribution_date = $line->contribution_date;
    $wanted_date_part = substr($wanted_contribution_date, 0, 10);

    try {
      $transaction = \Civi\Api4\CampagnodonTransaction::get()
        ->setCheckPermissions(false)
        ->addWhere('idx', '=', $idx)
        ->execute()
        ->single();

      $current_contribution_date = $transaction['contribution_date'];
      $current_date_part = substr($current_contribution_date, 0, 10);
      if ($current_date_part === $wanted_date_part) {
        echo "  contribution_date already ok (".$current_contribution_date.' ~ '.$wanted_contribution_date.")\n";
      } else {
        echo "  contribution_date must be set from ".$current_contribution_date." to ".$wanted_contribution_date."\n";
        if (!$test_mode) {
          \Civi\Api4\CampagnodonTransaction::update()
            ->setCheckPermissions(false)
            ->addValue('contribution_date', $wanted_contribution_date)
            ->addWhere('idx', '=', $idx)
            ->execute();
          echo "  transaction updated.\n";
        }
      }

      $contributions = \Civi\Api4\Contribution::get()
        ->setCheckPermissions(false)
        ->addJoin(
          'CampagnodonTransactionLink AS tlink',
          'INNER', null,
          ['tlink.entity_table', '=', '"civicrm_contribution"'],
          ['tlink.entity_id', '=', 'id']
        )
        ->addWhere('tlink.campagnodon_tid', '=', $transaction['id'])
        ->execute();

      foreach ($contributions as $contribution) {
        echo "  Contribution ".$contribution['id']."\n";
        $current_receive_date = $contribution['receive_date'];
        $current_date_part = substr($current_receive_date, 0, 10);
        if ($current_date_part === $wanted_date_part) {
          echo "    receive_date already ok (".$current_receive_date.' ~ '.$wanted_contribution_date.")\n";
        } else {
          echo "    receive_date must be set from ".$current_receive_date." to ".$wanted_contribution_date."\n";
          if (!$test_mode) {
            \Civi\Api4\Contribution::update()
              ->setCheckPermissions(false)
              ->addValue('receive_date', $wanted_contribution_date)
              ->addWhere('id', '=', $contribution['id'])
              ->execute();
          echo "    contribution updated.\n";
          }
        }
      }
    } catch (Throwable $e) {
      echo "  ERROR: ".$e."\n";
    }
  }
}
do_fix($test_mode, $json);

