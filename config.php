<?php
/**
 * config.php  - contain all constants definitions
 * dbconnection parameters
 * table name constants
 * auxiliary parameters
 */

/**
 * define main root host
 *
 * @var string
 */
//define("MAIN_HOST","http://ccss.confidentialconnections.com/");
define("MAIN_HOST","http://requestor.ualadys.com/");

/**#@+
 * define main database connection parameters DBserver,DBname.DBUser, DBPassword
 *
 * @var string
 */
session_start();

$myServer = "db-priv";
$myUser = "ccss";
$myPass = "Gjvjubrkbtyne";
$myDB = "ccss";

/**#@-*/

/**
 * define dbtable names prefix
 *
 * @var string
 */
define("PREFIX", "rt_");

/**#@+
 * define database table names
 *
 * @var string
 */
define("T_SESSION" , "ccss.rt_session");
define("T_USERS", "support.users");
define("T_GROUPS", PREFIX . "groups");
define("T_USERS_GROUPS", PREFIX . "users_groups");
define("T_TICKETS", PREFIX . "tickets");
define("T_TICKETS_ACTIONS", PREFIX . "tickets_actions");
define("T_BODIES", PREFIX . "bodies");
define("T_TEMPLATES", PREFIX . "templates");
define("T_TEMPLATE_VARS", PREFIX . "template_vars");
define("T_LOGIN_HISTORY", PREFIX . "login_history");
define("T_SIGNATURES", PREFIX . "signatures");
/**#@-*/

/**
 * define session life time
 *
 * @var string
 */
define("MAXLIFETIME", 555400);
//define("MAXLIFETIME", 5400);

/**
 * define default value of rows showed per page
 *
 * @var string
 */
define("DEFAULT_NUM_ROWS", 50);

/**
 * unknown parameter, always = true, used in update.php in some if constructs
 *
 * @var string
 */
define("__CFG_SEND_EMAIL", 1);

/**
 * define main menu items for admin and member groups
 *
 * @var string
 */
$main_menu = array( "admin"=>array(
                                  "Main"=>"index.php",
                                  "Search tickets"=>"search_tickets.php",
                                  "Templates"=>"templates.php",
                                  "Queues"=>"queues.php",
                                  "Users"=>"users.php",
                                  "Complains"=>"complains.php",
                                  "Stat"=>"stat.php",
                                  "Brakes"=>"brakes.php",
                                  "Logout"=>"logout.php"
                                  ),

                    "member"=>array(
                                  "Main"=>"index.php",
                                  "Search tickets"=>"search_tickets.php",
                                  "Stat"=>"stat.php",
				  "Profile"=>"profile.php",
                                  "Logout"=>"logout.php"
                                  )
                    );
?>
