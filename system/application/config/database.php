<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
  | -------------------------------------------------------------------
  | DATABASE CONNECTIVITY SETTINGS
  | -------------------------------------------------------------------
  | This file will contain the settings needed to access your database.
  |
  | For complete instructions please consult the "Database Connection"
  | page of the User Guide.
  |
  | -------------------------------------------------------------------
  | EXPLANATION OF VARIABLES
  | -------------------------------------------------------------------
  |
  |	['hostname'] The hostname of your database server.
  |	['username'] The username used to connect to the database
  |	['password'] The password used to connect to the database
  |	['database'] The name of the database you want to connect to
  |	['dbdriver'] The database type. ie: mysql.  Currently supported:
  mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
  |	['dbprefix'] You can add an optional prefix, which will be added
  |				 to the table name when using the  Active Record class
  |	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
  |	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
  |	['cache_on'] TRUE/FALSE - Enables/disables query caching
  |	['cachedir'] The path to the folder where cache files should be stored
  |	['char_set'] The character set used in communicating with the database
  |	['dbcollat'] The character collation used in communicating with the database
  |
  | The $active_group variable lets you choose which connection group to
  | make active.  By default there is only one group (the "default" group).
  |
  | The $active_record variables lets you determine whether or not to load
  | the active record class
 */

$active_group = "default";
$active_record = TRUE;



if (configuration_profile == 1) {
    //for local machine
    $db['default']['hostname'] = "localhost";
    $db['default']['username'] = "root";
    $db['default']['password'] = "password";
    $db['default']['database'] = "tooblerc_gaishan";
    $db['default']['dbdriver'] = "mysql";
} elseif (configuration_profile == 2) {
    // for toobler server
    $db['default']['hostname'] = "localhost";
    $db['default']['username'] = "tooblerc_gaishan";
    $db['default']['password'] = "#sKa]V,QM5])";
    $db['default']['database'] = "tooblerc_gaishan";
    $db['default']['dbdriver'] = "mysql";
} elseif (configuration_profile == 3) {
    // for eckhard's test system
    $db['default']['hostname'] = "localhost";
    $db['default']['username'] = "bto";
    $db['default']['password'] = "bto";
    $db['default']['database'] = "tooblerc_gaishan";
    $db['default']['dbdriver'] = "mysql";
} elseif (configuration_profile == 4) {
    // for MCon live system
    $db['default']['hostname'] = "localhost";
    $db['default']['username'] = "eschneid";
    $db['default']['password'] = "eeng2huJ";
    $db['default']['database'] = "tooblerc_gaishan";
    $db['default']['dbdriver'] = "mysql";
} elseif (configuration_profile == 5) {
    // for MCon live system
    $db['default']['hostname'] = "localhost";
    $db['default']['username'] = "eschneid";
    $db['default']['password'] = "eeng2huJ";
    $db['default']['database'] = "tooblerc_gaishan";
    $db['default']['dbdriver'] = "mysql";
} elseif (configuration_profile == 6) {
    // for MCon live system
    require_once "/etc/tracking/config_blacktri_db.inc.php";
}

$db['default']['pconnect'] = false;

/* End of file database.php */
/* Location: ./system/application/config/database.php */