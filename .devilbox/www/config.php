<?PHP
// Measure time
$TIME_START = microtime(true);

// Turn on all PHP errors
error_reporting(-1);

// Shorten DNS timeouts for gethostbyname in case DNS server is down
putenv('RES_OPTIONS=retrans:1 retry:1 timeout:1 attempts:1');


$DEVILBOX_VERSION = 'v0.9';
$DEVILBOX_DATE = '2017-05-10';

//
// Set Directories
//
$CONF_DIR	= dirname(__FILE__);
$INCL_DIR	= $CONF_DIR . DIRECTORY_SEPARATOR . 'include';
$LIB_DIR	= $INCL_DIR . DIRECTORY_SEPARATOR . 'lib';
$VEN_DIR	= $INCL_DIR . DIRECTORY_SEPARATOR . 'vendor';
$LOG_DIR	= dirname(dirname($CONF_DIR)) . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'devilbox';


require $LIB_DIR . DIRECTORY_SEPARATOR . '_iBase.php';
require $LIB_DIR . DIRECTORY_SEPARATOR . '_Base.php';



//
// Set Docker addresses
//
$DNS_HOST_NAME		= 'bind';
$PHP_HOST_NAME		= 'php';
$HTTPD_HOST_NAME	= 'httpd';
$MYSQL_HOST_NAME	= 'mysql';
$PGSQL_HOST_NAME	= 'pgsql';
$REDIS_HOST_NAME	= 'redis';
$MEMCD_HOST_NAME	= 'memcached';


//
// Lazy Loader
//
function loadFile($class) {
	static $_LOADED_FILE;

	if (isset($_LOADED_FILE[$class])) {
		return;
	}

	require $GLOBALS['LIB_DIR'] . DIRECTORY_SEPARATOR . $class . '.php';
	$_LOADED_FILE[$class] = true;
	return;
}
function loadClass($class) {

	static $_LOADED_LIBS;

	if (isset($_LOADED_LIBS[$class])) {
		return $_LOADED_LIBS[$class];
	} else {
		switch($class) {

			case 'Logger':
				loadFile($class);
				$_LOADED_LIBS[$class] = \devilbox\Logger::getInstance();
				break;

			case 'Docker':
				loadFile($class);
				$_LOADED_LIBS[$class] = \devilbox\Docker::getInstance();
				break;

			case 'Php':
				loadFile($class);
				$_LOADED_LIBS[$class] = \devilbox\Php::getInstance($GLOBALS['PHP_HOST_NAME']);
				break;

			case 'Dns':
				loadFile($class);
				$_LOADED_LIBS[$class] = \devilbox\Dns::getInstance($GLOBALS['DNS_HOST_NAME']);
				break;

			case 'Httpd':
				loadFile($class);
				$_LOADED_LIBS[$class] = \devilbox\Httpd::getInstance($GLOBALS['HTTPD_HOST_NAME']);
				break;

			case 'Mysql':
				loadFile($class);
				$_LOADED_LIBS[$class] = \devilbox\Mysql::getInstance(\devilbox\Mysql::getIpAddress($GLOBALS['MYSQL_HOST_NAME']), 'root', loadClass('Docker')->getEnv('MYSQL_ROOT_PASSWORD'));
				break;

			case 'Pgsql':
				loadFile($class);
				$_LOADED_LIBS[$class] = \devilbox\Pgsql::getInstance(\devilbox\Pgsql::getIpAddress($GLOBALS['PGSQL_HOST_NAME']), loadClass('Docker')->getEnv('PGSQL_ROOT_USER'), loadClass('Docker')->getEnv('PGSQL_ROOT_PASSWORD'));
				break;

			case 'Redis':
				loadFile($class);
				$_LOADED_LIBS[$class] = \devilbox\Redis::getInstance(\devilbox\Redis::getIpAddress($GLOBALS['REDIS_HOST_NAME']));
				break;

			case 'Memcd':
				loadFile($class);
				$_LOADED_LIBS[$class] = \devilbox\Memcd::getInstance(\devilbox\Memcd::getIpAddress($GLOBALS['MEMCD_HOST_NAME']));
				break;

			// Get optional docker classes
			default:
				// Redis
				exit('Class does not exist: '.$class);
		}
		return $_LOADED_LIBS[$class];
	}
}



// VirtualHost DNS check
// Temporarily disable due to:
// https://github.com/cytopia/devilbox/issues/8
$ENABLE_VHOST_DNS_CHECK = false;
