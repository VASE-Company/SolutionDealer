<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['suspendedSystem'] = false;
$config['applicationName'] = 'COMPRAS CORPORATIVAS TEST';
$config['recordsPerPage'] = 10;

/*
$config['panelConfig'] = array('daysBudgetToCall'=>5,
                               'daysBudgetUnanswered'=>4,
                               'daysBudgetUnread'=>3);
*/

$config['files'] = "./files/";
$config['images'] = "./assets/images/";

$config['priorityIdDefault'] = 3;
$config['stateIdDefault'] = "TOAUT";

$config['superUserId'] = 1;

$config['extensionsOfImageUser'][0] = "jpg";
$config['extensionsOfImageUser'][1] = "png";
$config['extensionsOfImageUser'][2] = "gif";
$config['extensionsOfImageUser'][3] = "jpeg";

$config['deliveryNoteMovementTypeId'] = 1;
$config['outputStockMovementTypeId'] = 2;
$config['inputStockMovementTypeId'] = 3;
$config['billMovementTypeId'] = 4;

$config['systemMailConfig'] = array('host'=>'mail.solutiondealer.com.ar',
                                    'user'=>'compras@solutiondealer.com.ar',
                                    'password'=>'M4iLC0mPr4S',
                                    'responseTo'=>'clientes@solutiondealer.com',
                                    'copyTo'=>'cristianmdq@hotmail.com',
                                	  'send'=>false,
                                	  'logOK'=>false,
                                	  'logERROR'=>false);

$config['notificationsConfig'] = array('markRead'=>true);

$config['taskKey'] = 'hr6FRWn404sjcyieVmTMBjwi2oeQ5SdO';

$config['userPass'] = 'Ps6kuPZMrP2mjSGHDDrys7RA';

/* End of file myApplication.php */
/* Location: ./application/config/myApplication.php */