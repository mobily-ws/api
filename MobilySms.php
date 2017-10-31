<?php
class MobilySms {
    public $userName;
    public $password;
    public $apiKey;
    public $result;
    private $method;
    private $json = array();
    public $error = "";

	/**
	 * Check if user information is it API or mobile and password
     * and set curl as default send  method
     *
     * @param string $userName The Account username or mobile in mobily.ws
     * @param string $password The password of mobily account
     * @param string $apiKey The api key from  mobily account
	 **/
    function __construct($userName=NULL, $password=NULL, $apiKey=NULL) {
        if (!empty($apiKey)){
            $this->apiKey = $apiKey;
        }elseif(!empty($userName)&&!empty($userName)){
            $this->userName = $userName;
            $this->password = $password;
        }
		$this->method = 'curl';
    }

    /**
     * Check if user information is it API or mobile and password And if
     * this information is not empty set in variables for all api function other return error
     *
     * @param string $userName The Account username or mobile in mobily.ws
     * @param string $password The password of mobily account
     * @param string $apiKey The api key from  mobily account
     * @return string $this->error If there is no error, it doesn't return anything
     **/
    public function setInfo($userName=NULL,   $password=NULL, $apiKey=NULL) {
		if(empty($userName) && empty($password) && empty($apiKey)) {
			$this->error = 'Please Insert Data';	
		} elseif (!empty($apiKey)) {
			$this->apiKey = $apiKey;
		} elseif(!empty($userName) & !empty($password)) {
			$this->userName = $userName;
            $this->password = $password;			
		}
		return $this->error;
    }

    /**
     * Check if user information is not empty and
     * prepare information in array to Merge with another message data
     * you can call this function just in api function because it's private
     *
     **/
    private function checkUserInfo() {
		$this->json = array();
		$this->error = "";
        if (!empty($this->apiKey)) {
            $this->json=array("apiKey"=>$this->apiKey);
        } elseif (!empty($this->userName) && !empty($this->password)) {
            $this->json=array("mobile"=>$this->userName,"password"=>$this->password);
        } else {
            $this->error = 'insert APIKEY or Username and Password';
        }
    }

    /**
     * Using  send method you'r selected in api function and
     * if doesn't match with any cases return error
     *
     * @param string $host The Account username or mobile in mobily.ws (required)
     * @param string $path The password of mobily account(required)
     * @param string $data Message data
     * @return string $this->error If any error found
     * @return string $this->result If there is no error , it's json report from mobily.ws
     **/
    private function run($host,$path,$data='') {
        switch ($this->method) {
            case 'curl':
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $host.$path);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                $this->result = curl_exec($ch);
                break;
            case 'fsockopen':
                $host=str_replace('https://','',$host);
                $host=str_replace('http://','',$host);
                $length = strlen($data);
                $fsockParameter = "POST ".$path." HTTP/1.0 \r\n";
                $fsockParameter .= "Host: ".$host." \r\n";
                $fsockParameter .= "Content-type: application/x-www-form-urlencoded \r\n";
                $fsockParameter .= "Content-length: $length \r\n\r\n";
                $fsockParameter .= "$data";
                $fsockConn = fsockopen($host, 80, $errno, $errstr, 30);
                fputs($fsockConn, $fsockParameter);
                $clearResult = false;
                while (!feof($fsockConn)) {
                    $line = fgets($fsockConn, 10240);
                    if ($line == "\r\n" && !$clearResult)
                        $clearResult = true;
                    if ($clearResult)
                        $this->result = trim($line);
                }
                break;
            case 'fopen':
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $data, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $handle = fopen($host.$path, 'r', false, $contextResouce);
                $this->result = stream_get_contents($handle);
                break;
            case 'file':
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $data, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $arrayResult = file($host.$path, FILE_IGNORE_NEW_LINES, $contextResouce);
                $this->result = $arrayResult[0];
                break;
            case 'file_get_contents':
                $contextOptions['http'] = array('method' => 'json', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $data, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $this->result = file_get_contents($host.$path, false, $contextResouce);
                break;
            default:
                $this->error = 'active one of the following portals (curl,fopen,fsockopen,file,file_get_contents) on server';
                return $this->error;
        }
        return $this->result;
    }


    /**
     * Send  message directly without separate message data
     * you can use to call function (sendMsg Or sendMsgWK).
     *
     * @param string $functionName Name of the function (required)
     * @param string $data Message data (required)
     * @return string $this->error If any error found
     * @return string $this->result If there is no error , it's json report from mobily.ws
     **/
    public function callAPI ($functionName, $data,$port=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($port);
        if(empty($this->error)) {
            $this->json=array_merge($this->json,$data);
            $this->json['numbers']=explode(',',$this->json['numbers']);
            $this->json['lang']='3';
            $this->json=json_encode($this->json);
            switch ($functionName) {
                case 'sendMsg':
                        return $this->run('http://mobily.ws', '/api/msgSend.php', $this->json);
                    break;
                case 'sendMsgWK':
                        return $this->run('http://mobily.ws', '/api/msgSendWK.php', $this->json);
                    break;
                default:
                    $this->error[] = 'method name not found You can select either (sendMsg,sendMsgWK).';
                    return $this->error;
            }
        }else{
            return $this->error;
        }
    }

    /**
     * Check if send method selected in function and
     * test send method if work or if method doesn't selected
     * test method  and choose which works
     *
     * @param string $method Send method
     * @return string $this->error If not empty method
     **/
	private function getSendMethod($method=NULL) {
		//Change Deafult Method
		if(!empty($method)){
			$this->method = strtolower($method);
		}
		//Check CURL
		if($this->method == 'curl') {
			if(function_exists("curl_init") && function_exists("curl_setopt") && function_exists("curl_exec") && function_exists("curl_close") && function_exists("curl_errno")) {
				return 1;
			} else {
				if(!empty($method)) {
					return $this->error = 'CURL is not supported';
				} else {
					$this->method = 'fsockopen';
				}
			}			
		}
		//Check fSockOpen
		if($this->method == 'fsockopen') {
			if(function_exists("fsockopen") && function_exists("fputs") && function_exists("feof") && function_exists("fread") && function_exists("fclose")) {
				return 1;
			} else {
				if(!empty($method)) {
					return $this->error = 'fSockOpen is not supported';
				} else {
					$this->method = 'fopen';
				}
			}			
		}
		//Check fOpen
		if($this->method == 'fopen') {
			if(function_exists("fopen") && function_exists("fclose") && function_exists("fread")) {
				return 1;
			} else {
				if(!empty($method)) {
					return $this->error = 'fOpen is not supported';
				} else {
					$this->method = 'file_get_contents';
				}
			}			
		}
		//Check File
		if($this->method == 'file') {
			if(function_exists("file") && function_exists("http_build_query") && function_exists("stream_context_create")) {
				return 1;
			} else {
				if(!empty($method)) {
					return $this->error = 'File is not supported';
				} else {
					$this->method = 'file_get_contents';
				}
			}			
		}
		//Check file_get_contents
		if($this->method == 'file_get_contents') {
			if(function_exists("file_get_contents") && function_exists("http_build_query") && function_exists("stream_context_create")) {
				return 1;
			} else {
				if(!empty($method)) {
					return $this->error = 'file_get_contents is not supported';
				} else {
					$this->method=NULL;
				}
			}			
		}				
    }

    /**
     * Send message
     *
     * @param string $message (required)
     * @param string $numbers Numbers to send (between each number comma ",")(required)
     * @param string $sender Name of message sender (required)
     * @param integer $timeSend Time to send message like this 17:30:00
     * @param integer $dateSend Date to send message like this 6/30/2017
     * @param integer $notRepeat 1 -> Delete repeated numbers 0 -> Allow repeated numbers
     * @param string $deleteKey Key to delete message using deleteKey function
     * @param string $method Send method
     * @return string $this->error If any error found
     */
    public function sendMsg($message, $numbers, $sender,$timeSend='', $dateSend='',$notRepeat=0,$deleteKey='', $method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            $numbers = explode(',',$numbers);
            $data = array(
				'numbers'=>$numbers,
                'sender'=>$sender,
                'msg'=>$message,
                'timeSend'=>$timeSend,
                'dateSend'=>$dateSend,
                'notRepeat'=>$notRepeat,
                'deleteKey'=>$deleteKey,
                'lang'=>'3'
            );
            $this->json = array_merge($this->json, $data);
            $this->json = json_encode($this->json);
            return $this->run('http://mobily.ws','/api/msgSend.php',$this->json);
        }
        return $this->error;
    }

    /**
     * Send message with key
     * Configuration a message by replacing the symbols and and data in msgKey
     * and placing their data in the message ,
     * and the number of data must match the count of numbers
     *
     * @param string $message (required)
     * @param string $numbers Numbers to send (between each number comma ",")(required)
     * @param string $sender Name of message sender (required)
     * @param string $msgKey Template of message (required)
     * @param integer $timeSend Time to send message like this 17:30:00
     * @param integer $dateSend Date to send message like this 6/30/2017
     * @param integer $notRepeat 1 -> Delete repeated numbers 0 -> Allow repeated numbers
     * @param string $deleteKey Key to delete message using deleteKey function
     * @param string $method Send method
     * @return string $this->error If any error found
     */
    public function sendMsgWK($message, $numbers, $sender, $msgKey, $timeSend=0, $dateSend=0,$notRepeat=0, $deleteKey='',$method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            $numbers=explode(',',$numbers);
            $data=array(
                'numbers'=>$numbers,
                'sender'=>$sender,
                'msgKey'=>$msgKey,
                'msg'=>$message,
                'timeSend'=>$timeSend,
                'notRepeat'=>$notRepeat,
                'dateSend'=>$dateSend,
                'deleteKey'=>$deleteKey,
                'applicationType'=>'68',
                'lang'=>'3'
            );
            $this->json=array_merge($this->json,$data);
            $this->json=json_encode($this->json);

            return $this->run('http://mobily.ws','/api/msgSendWK.php',$this->json);
        }
        return $this->error;
    }

    /**
     * Get send status
     *
     * @param string $method Send method
     * @return string $this->result
     **/
    public function sendStatus($method=NULL) {
        $this->getSendMethod($method);
        $data=array(
            'returnJson'=>'1'
        );
        $this->json=array_merge($this->json,$data);
        $this->json=json_encode($this->json);
        return $this->run('http://mobily.ws','/api/sendStatus.php','returnJson=1');
    }

    /**
     * Change mobily account password
     *
     * @param string $oldPassword Mobily account password (required)
     * @param string $newPassword New mobily account password (required)
     * @param string $method Send method
     * @return string $this->error If any error found
     * @return string $this->result If there is no error
     **/
    public function changePassword($oldPassword,$newPassword,$method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {	
            if(!empty($this->json['apiKey'])){
                $userInfo=array('apiKey'=>$this->json['apiKey']);
            }else{
                $userInfo=array('mobile'=>$this->json['mobile']);
            }
            $data=array(
                'password'=>$oldPassword,
                'newPassword'=>$newPassword
            );
            $this->json=array_merge($userInfo,$data);
            $this->json=json_encode($this->json);
            return $this->run('http://mobily.ws','/api/changePassword.php',$this->json);
        }
        return $this->error;
    }

    /**
     * Send account password to account email or number
     *
     * @param integer $sendType Send password type 1 -> to number 2 -> email (required)
     * @param string $method Send method
     * @param string $lang return value language (ar or en)
     * @return string $this->error If any error found
     * @return string $this->result If there is no error
     **/
    public function forgetPassword($sendType,$lang,$method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            if(!empty($this->json['apiKey'])){
                $userInfo=array('apiKey'=>$this->json['apiKey']);
            }else{
                $userInfo=array('mobile'=>$this->json['mobile']);
            }
            $lang=strtolower($lang);
            if($lang!='ar'&&$lang!='en'){
                $lang='ar';
            }
            $data=array('type'=>$sendType,'lang'=>$lang);
            $this->json=array_merge($userInfo,$data);
            $this->json=json_encode($this->json);
            return $this->run('http://mobily.ws','/api/forgetPassword.php',$this->json);
        }
        return $this->error;
    }

    /**
     * Get balance of mobily account
     *
     * @param string $method Send method
     * @return string $this->error If any error found
     * @return string $this->result If there is no error
     **/
    public function balance($method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            $this->json=json_encode($this->json);
            return $this->run('http://mobily.ws','/api/balance.php',$this->json);
        }
        return $this->error;
    }

    /**
     * Delete message Using message deleteKey
     *
     * @param string $deleteKey Message deleteKey to delete message (required)
     * @param string $method Send method
     * @return string $this->error If any error found
     * @return string $this->result If there is no error
     **/
    public function deleteMsg($deleteKey,$method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            $data=array('deleteKey'=>$deleteKey);
            $this->json=array_merge($this->json,$data);
            $this->json=json_encode($this->json);
            return $this->run('http://mobily.ws','/api/deleteMsg.php',$this->json);
        }
        return $this->error;
    }

    /**
     * Request number as sender name From mobily
     *
     * @param string $sender Sender name(required)
     * @param string $lang return value language (ar or en)
     * @param string $method Send method
     * @return string $this->error If any error found
     */
    public function addSender($sender,$lang='',$method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            $lang=strtolower($lang);
            if($lang!='ar'&&$lang!='en'){
                $lang='ar';
            }
            $data=array('sender'=>$sender,'lang'=>$lang);
            $this->json=array_merge($this->json,$data);
            $this->json=json_encode($this->json);
            return $this->run('http://mobily.ws','/api/addSender.php',$this->json);
        }
        return $this->error;
    }

    /**
     * Activate the sender name (number) which was requested
     *
     * @param integer $senderId Id of Sender name was requested(required)
     * @param integer $activeKey Key which sent to mobile(required)
     * @param string $method Send method
     * @return string $this->error If any error found
     * @return string $this->result If there is no error
     **/
    public function activeSender($senderId,$activeKey,$method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            $data=array(
                'senderId'=>$senderId,
                'activeKey'=>$activeKey,
                );
            $this->json=array_merge($this->json,$data);
            $this->json=json_encode($this->json);
            return $this->run('http://mobily.ws','/api/activeSender.php',$this->json);
        }
        return $this->error;
    }

    /**
     * Check if the sender name (number) which was requested is active or not
     *
     * @param integer $senderId Id of Sender name was requested(required)
     * @param string $method Send method
     * @return string $this->error If any error found
     * @return string $this->result If there is no error
     **/
    public function checkSender($senderId,$method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            $data=array(
                'senderId'=>$senderId,
            );
            $this->json=array_merge($this->json,$data);
            $this->json=json_encode($this->json);
            return $this->run('http://mobily.ws','/api/checkSender.php',$this->json);
        }
        return $this->error;
    }

    /**
     * Add sender name as text
     *
     * @param string $sender Sender name (required)
     * @param string $method Send method
     * @return string $this->error If any error found
     * @return string $this->result If there is no error
     **/
    public function addAlphaSender($sender,$method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            $data=array(
                'sender'=>$sender,
            );
            $this->json=array_merge($this->json,$data);
            $this->json=json_encode($this->json);
            return $this->run('http://mobily.ws','/api/addAlphaSender.php',$this->json);
        }
        return $this->error;
    }

    /**
     * Check if the sender name (number) which was requested is active or not
     *
     * @param string $method Send method
     * @return string $this->error If any error found
     * @return string $this->result If there is no error
     **/
    public function checkAlphaSender($method=NULL) {
        $this->checkUserInfo();
        $this->getSendMethod($method);
        if(empty($this->error)) {
            $this->json=json_encode($this->json);
            return $this->run('http://mobily.ws','/api/checkAlphasSender.php',$this->json);
        }
        return $this->error;
    }
}