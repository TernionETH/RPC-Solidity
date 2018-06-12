<?php
/*
The MIT License
Copyright 2018 ANATOLII LYTVYNENKO 

www:	http://aizo.club
email:	dev[at]aizo.club

Permission is hereby granted, free of charge, to any person obtaining 
a copy of this software and associated documentation files (the "Software"), 
to deal in the Software without restriction, including without limitation 
the rights to use, copy, modify, merge, publish, distribute, sublicense, 
and/or sell copies of the Software, and to permit persons to whom the Software 
is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies 
or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR 
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE 
FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, 
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

/*
$contract="
pragma solidity ^0.4.13;
contract owned {
    address public _owner;
    function owned() {
		_owner = msg.sender;
    }
	
    modifier onlyOwner {
        require(msg.sender == _owner);
        _;
    }

    function ChangeOwnership(address _newOwner) onlyOwner returns (bool){
        _owner = _newOwner;
		return true;
    }
}
";

$_orpc= new rpc();
//$data=array('execute'=>'solc','command'=>'abi','xauth'=>'KJnI98imob47io','contract'=>$contract);
$data=array('execute'=>'solc','command'=>'compile','xauth'=>'KJnI98imob47io','contract'=>$contract);
$r=$_orpc->send('ondemand',array($data));
*/

class rpc{
	private $_rpc_settings=array();
	const JSON_RPC_VERSION = '2.0';
	const JSON_RPC_MEDIA_TYPE = 'application/json';
		
	public function __construct($host='127.0.0.1',$port='7777',$network=1){
		$this->_rpc_settings['host']=$host;
		$this->_rpc_settings['port']=$port;
	}

	public function send($_callmethod='',$args=array()){
		$request = array(
			'jsonrpc' => self::JSON_RPC_VERSION,
			'method' => $_callmethod,
			'params' => array_values($args),
			'id' => $this->_rpc_settings['network_id'],
		);
		
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type:'.self::JSON_RPC_MEDIA_TYPE));
		curl_setopt($ch, CURLOPT_URL,$this->_rpc_settings['host']);
		curl_setopt($ch, CURLOPT_PORT, $this->_rpc_settings['port']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($request,JSON_UNESCAPED_UNICODE));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$_r= curl_exec($ch);
		$_e= curl_errno($ch);
		curl_close ($ch);
		if(!empty($_e)){
			switch($_e){
				case 7: return array('_err'=>array('NETWORK_UNREACHABLE'));
				default:  return array('_err'=>array('NETWORK_ERROR_UNDEFINED'));
			}
		}
		return @json_decode($_r,true);
	}
}
?>