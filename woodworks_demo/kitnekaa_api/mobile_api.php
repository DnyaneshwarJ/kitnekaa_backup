<?php

$client = new SoapClient('http://kitnekaa.com/kitnekaa_demo/api/soap/?wsdl');

// If somestuff requires api authentification,
// then get a session token
$session = $client->login('mobile_user', '8495ba759ccf217cb06110017332c49f');
$action = $_REQUEST['operation'];//catalog_product.list

switch($action)
{
		case 'product_list':
		$operation = 'catalog_product.list';
		break;
		case 'category_list':
		$operation = 'category.tree';
		break;
}

$result = $client->call($session, $operation);
/*echo "<pre>";
print_r($result);
echo "</pre>";
*/
$final_result= array('result'=>$result);
echo json_encode($final_result);

// If you don't need the session anymore
//$client->endSession($session);