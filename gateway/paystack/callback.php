<?php
/*
	#	Paystack Payment module callback for Sendroid Ultimate
	#	location: modules/paystack/index.php
	#	Developed by: Ynet Interactive
	#	Special thanks: Mr. White

*/
define('ENVIRONMENT', 'production');
ob_start();
session_start();
$URL = 'dashboard';

if (defined('ENVIRONMENT'))
{
	switch (ENVIRONMENT)
	{
		case 'development':
			error_reporting(E_ALL);
		break;
	
		case 'testing':
		case 'production':
			error_reporting(0);
		break;

		default:
			exit('The application environment is not set correctly.');
	}
}
$system_path = '../../system';
$application_folder = 'hooks';
$module_folder = 'modules';
	// Set the current directory correctly for CLI requests
	if (defined('STDIN'))
	{
		chdir(dirname(__FILE__));
	}

	if (realpath($system_path) !== FALSE)
	{
		$system_path = realpath($system_path).'/';
	}

	// ensure there's a trailing slash
	$system_path = rtrim($system_path, '/').'/';
	// Is the system path correct?
	if ( ! is_dir($system_path))
	{
		exit("Your system folder path does not appear to be set correctly. Please open the following file and correct this: ".pathinfo(__FILE__, PATHINFO_BASENAME));
	}
	// The name of THIS file
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
	// The PHP file extension
	// this global constant is deprecated.
	define('EXT', '.php');
	// Path to the system folder
	define('BASEPATH', str_replace("\\", "/", $system_path));
	// Path to the front controller (this file)
	define('FCPATH', str_replace(SELF, '', __FILE__));
	// Name of the "system folder"
	define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));

	// The path to the "application" folder
	if (is_dir($application_folder))
	{
		define('APPPATH', $application_folder.'/');
		define('MODPATH', $module_folder.'/');
	}
	else
	{
		if ( ! is_dir(BASEPATH.$application_folder.'/'))
		{
			exit("Your application folder path does not appear to be set correctly. Please open the following file and correct this: ".SELF);
		}

		define('APPPATH', BASEPATH.$application_folder.'/');
		define('MODPATH', BASEPATH.$module_folder.'/');
	}
define('COR', BASEPATH.'core/');	
define('LIB', BASEPATH.'libraries/');	
define('HELP', BASEPATH.'helpers/');	
require_once '../../root.php';
require_once BASEPATH.'core/Config.php';
foreach (glob(COR."/*.php") as $filename) { include $filename; }
foreach (glob(HELP."/*.php") as $filename) { include $filename; }
foreach (glob(LIB."/*.php") as $filename) { include $filename; }
$Config = new CI_Config();
setTimeZone();
require_once BASEPATH.'lang/'.setLang(1);
$is_valid = 0;
$response = $LANG['Payment verification failed'];
//Load saved transaction from Session is available
$Transaction_ref = $_SESSION['reference'];
$Payment_gateway = $_SESSION['gateway'];
$user_id = $_SESSION['tx_user'];
if(!isset($_SESSION['reference']) || empty($_SESSION['reference'])) {
	//Get reference from query strings
	$Transaction_ref = mysqli_real_escape_string($server,$_REQUEST['tx_reference']); 
	$result = mysqli_query($server,"SELECT * FROM transactions WHERE transaction_reference = '$Transaction_ref'"); 
	$row = mysqli_fetch_assoc($result);
	$Transaction_id = $row['id'];
	$user_id = $row['customer_id'];
	$Payment_gateway = $row['method'];
}
if($Transaction_ref!="") {
	$_SESSION['reference'] = $Transaction_ref;
	$comments = 'Your payment was not successful';
	$result = array();
	$url = 'https://api.paystack.co/transaction/verify/'.$Transaction_ref;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
        'Authorization: Bearer '. trim(paymentGatewayData($Payment_gateway,'param2')),		//That is Paystack Secret key
        'Content-Type: application/json'
        )
    );
	$request = curl_exec($ch);
	$curl_err = curl_error($ch);
	curl_close($ch);
	
	if ($request) {
	 	$result = json_decode($request, true);
		if (array_key_exists('data', $result) && array_key_exists('status', $result['data']) && ($result['data']['status'] === 'success')) {
			$is_valid = 1;
			$comments = 'Transaction Successful';
			$response = $result['data']['gateway_response'];
		} else {
			$is_valid = -1;
			$comments = $LANG['Transaction failed!'];
			$response = $result['data']['gateway_response'];
		}
		if(!isset($response)) { $response = $result['message']; }
	} else {
		$is_valid = -1;
		$response = $curl_err;	
	}
}

if($is_valid < 1) {
	$_SESSION['message'] = $comments.' '.$response;
	mysqli_query($server,"UPDATE transactions SET status = 'Failed' WHERE transaction_reference = '$Transaction_ref' LIMIT 1");
	mysqli_query($server,"UPDATE transactions SET gateway_response = '$response' WHERE transaction_reference = '$Transaction_ref' LIMIT 1");
	logEvent($user_id,'Transaction: '.$Transaction_ref.' failed');		
	header('location: ../../fund?confirm=0');		//pay attention to the path
	exit();
} else {
	processTransaction($Transaction_ref);
	header('location: ../../fund?confirm=1');		//pay attention to the path 
	exit();
}
?>
