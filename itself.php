<?

    require_once "object/object.class.php";
    require_once "loader/loader.class.php";
    require_once "scope/scope.class.php";
    require_once "factory/factory.class.php";
    require_once "factory/class/class.class.php";

    define('OXYGEN_JSON_RESONSE',1);
    define('OXYGEN_TEXT_RESONSE',2);
    define('OXYGEN_HTML_RESONSE',3);
    define('OXYGEN_XML_RESPONSE',4);
    define('OXYGEN_REDIRECT_RESPONSE',5);

    function o($tag = 'div') {
        if($tag{0}=='/'){
            Oxygen::close();
        } else {
            Oxygen::open($tag);
        }
    }

    function jsonResponse($data, $headers = array()) {
    	return array(
			'header' => 'Content-Type: application/json; Charset=UTF-8',
			'type'    => OXYGEN_JSON_RESONSE,
			'body'    => $data
    	);
    }

    function htmlResponse($data) {
    	return array(
			'header'  =>'Content-Type: text/html; Charset=UTF-8',
			'type'    => OXYGEN_HTML_RESONSE,
			'body'    => $data
    	);
    }


    function xmlResponse($data) {
    	return array(
			'header'  => 'Content-Type: application/xml; Charset=UTF-8',
			'type'    => OXYGEN_XML_RESONSE,
			'body'    => $data
    	);
    }

    function textResponse($data) {
    	return array(
			'header'  => 'Content-Type: text/plain; Charset=UTF-8',
			'type'    => OXYGEN_TEXT_RESONSE,
			'data'    => $data
    	);
    }

    function redirectResponse($data) {
    	return array(
			'header'  => 'Location:' . $data,
			'type'    => OXYGEN_REDIRECT_RESPONSE,
			'data'    => false
    	);
    }

    function registerOxygenCommons($scope) {
        $array = array(
            'LogonPage' => 'Oxygen_Common_LogonPage',
            'Authenticator' => 'Oxygen_Common_Authenticator',
            'Application' => 'Oxygen_Common_Application',
            'Page' => 'Oxygen_Common_Page'
        );
        foreach($array as $name => $class) {
            $scope->register($name, $class);
        }
    }


    function handleHttpRequest($scope, $root, $model = false, $debug = true) {
	    $scope->__setEnvironment(array(
	        'SERVER'    => $_SERVER,
	        'REQUEST'   => $_REQUEST,
	        'ENV'       => $_ENV,
	        'COOKIE'    => $_COOKIE,
	        'POST'      => $_POST,
	        'GET'       => $_GET,
	        'FILES'     => $_FILES,
	        'SESSION'   => $scope->Session()
	    ));
	    try {
	        if ($scope->assets->handled($scope->OXYGEN_PATH_INFO)) exit;
            registerOxygenCommons($scope);
	        $userScope = $scope->Scope();
	        $root = $userScope->$root($model);
	        $scope->httpStatus = 200;
			$scope->httpHeaders = array();
	        $root->setPath($scope->OXYGEN_ROOT_URI);
	        $last = $root[$scope->OXYGEN_PATH_INFO];
	        $result = $last->handleRequest();
	        if (is_string($result)) {
                if($result === '') $result=$scope->SERVER['REQUEST_URI'];
	        	$result = redirectResponse($result);
	        }
	        header($result['header']);
	        foreach($scope->httpHeaders as $h) {
	        	header($h);
	        }
            if(isset($result['body'])) {
    	        $body = $result['body'];
                if($body) {
	               	if(is_string($body)) echo $body;
	            	else call_user_func($body);
    	        }
            }
	    } catch(Exception $ex) {
	    	if ($debug) {
		        try {
		            $scope->__wrapException($ex)->put_page_view();
		        } catch(Exception $ex) {
		        	echo $ex->getMessage();
		            print_r($ex);
		        }
	    	} else {
	    		header('HTTP/1.0 500 OxygenError');
	    		echo $ex->getMessage();
	    	}
	    }
	}

    return Oxygen_Scope::newRoot(dirname(dirname(__FILE__)));




?>
