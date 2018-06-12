<?php
/*
The MIT License
Copyright 2017 ANATOLII LYTVYNENKO 

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

define('DOCUMENT_ROOT',__DIR__);
define('UPSTREAM_HOST','http://cdn.aizo.club');
define('DOCUMENT_ROOT_BIN',DOCUMENT_ROOT.DIRECTORY_SEPARATOR.'bin');

$allow_xauth_list=array(
	'KJnI98imob47io'
);

$rsd=readStreamData();
$stream=$rsd['params'][0];

if(!isset($stream['execute']) || !isset($stream['xauth']) || empty($stream['xauth'])){connection_close($stream);}
if(!in_array($stream['xauth'],$allow_xauth_list)){connection_close($stream);}

$execute=strtolower($stream['execute']);
unset($stream['execute']);

switch($execute){
	case 'solc':	if(!isset($stream['command'])){
						send_close_connect_headers();
					}
					
					switch($stream['command']){
						case 'abi':		$contract=(isset($stream['contract']) && !empty($stream['contract']))?$stream['contract']:false;
										if(!$contract){send_close_connect_headers();}
										$fso = new fso();
										$cfn=md5(microtime(true));
										$cfn=$fso->_split_string($cfn).'.sol';
										$tmpFilename=$fso->_build_work_path(DOCUMENT_ROOT,'tmp',$cfn);
										$fso->write($tmpFilename,$contract);
										$CompiledFilename=$fso->_build_work_path(DOCUMENT_ROOT,'build',$cfn);
										$outDir=dirname($CompiledFilename);
										
										$out=array();
										$ev=false;
										$cmd=sprintf("%s%ssolc --combined-json abi -o %s %s",DOCUMENT_ROOT_BIN,DIRECTORY_SEPARATOR,$outDir,$tmpFilename);

										@exec($cmd,$out,$ev);
										if($ev){connection_close($out,1);}
										
										$content=$fso->read($outDir.'/combined.json');
										$content=json_decode($content,true);
										$abi=array();
										$compact=array();

										foreach($content['contracts'] as $k => $v){
										    list(,$name)=explode(':',$k);
										    $abi[$name]=json_decode($v['abi'],true);
											if(!empty($abi[$name])){
												foreach($abi[$name] as $rec){
													if($rec['type']=='function'){
														$args=array();
														$fn=($rec['name']=='name')?$name:$rec['name'];
														if(!empty($rec['inputs']))
														foreach($rec['inputs'] as $rec2){$args[]=$rec2['type'];}
														$compact[$name][$fn]=array(
															'func'=>$fn,
															'args'=>$args,
															'call'=>sprintf("%s(%s)",$fn,implode(',',$args))
														);
													}		
													
												}
												
											}
										}
										@unlink($CompiledFilename);
										connection_close(array('abi'=>$abi,'compact'=>$compact,'out'=>$out,'ev'=>$ev));
										break;
							case 'compile':
							case 'compile-ast':
							case 'compile-ast-json':
							case 'compile-ast-compact-json':
							case 'compile-asm':
							case 'compile-asm-json':
							case 'compile-opcodes':
							case 'compile-bin':
							case 'compile-bin-runtime':
							case 'compile-clone-bin':
							case 'compile-abi':
							case 'compile-hashes':
							case 'compile-userdoc':
							case 'compile-devdoc':
							case 'compile-metadata':
									
									$contract=(isset($stream['contract']) && !empty($stream['contract']))?$stream['contract']:false;
									if(!$contract){send_close_connect_headers();}
									list(,$optN)=explode('-',$stream['command'],2);
									$opts=array(
										'ast',
										'ast-json',
										'ast-compact-json',
										'asm',
										'asm-json',
										'opcodes',
										'bin',
										'bin-runtime',
										'clone-bin',
										'abi',
										'hashes',
										'userdoc',
										'devdoc',
										'metadata'
									);
									$options=(in_array($optN,$opts))?sprintf('--%s',$optN):'--'.implode(' --',$opts);
									$fso = new fso();
									$cfn=md5(microtime(true));
									$cfn=$fso->_split_string($cfn).'.sol';
									$tmpFilename=$fso->_build_work_path(DOCUMENT_ROOT,'tmp',$cfn);
									$fso->write($tmpFilename,$contract);
									$CompiledFilename=$fso->_build_work_path(DOCUMENT_ROOT,'build',$cfn);
									$outDir=dirname($CompiledFilename);
										
									$out=array();
									$ev=false;
									$cmd=sprintf("%s%ssolc %s -o %s %s",DOCUMENT_ROOT_BIN,DIRECTORY_SEPARATOR,$options,$outDir,$tmpFilename);
									@exec($cmd,$out,$ev);
									$fx=glob(sprintf("%s%s*.*",$outDir,DIRECTORY_SEPARATOR));
									if(empty($fx)){connection_close(array('result'=>'run-time error','out'=>$out,'ev'=>$ev),1);}
									if(!empty($fx)){
										foreach($fx as $k => $path){
											$fx[$k]=str_replace(DOCUMENT_ROOT,UPSTREAM_HOST,$path);
										}
									}
									connection_close(array('files'=>$fx,'out'=>$out,'ev'=>$ev));
							break;
					}
	default: connection_close(array('result'=>'nothing to do','out'=>array(),'ev'=>array()),1);
}
/*--------------------------------------------------------------------------------------*/
function readStreamData(){
	$r=array();
	if($_SERVER['REQUEST_METHOD']=='POST'){
		switch($_SERVER['CONTENT_TYPE']){
			case 'application/json':	
				$_r=file_get_contents("php://input");
				$_r=@json_decode($_r,true);
				if(empty($_r)){return array();}
				break;
			default: $_r=$_POST;
		}
		$_r=array_merge($_GET,$_r);
		return $_r;
	}else{
		return $_GET;
	}
	return false;
}

function diff_file_time($fn1, $fn2 = false) {
	$mtime1 = @filemtime($fn1);
	if (!$fn2) {return $mtime1;}
	$mtime2 = @filemtime($fn2);
	return ($mtime2 && $mtime1 == $mtime2);
}

function connection_close($xx=array(),$error=false){
	$z=array('error'=>$error,'result'=>$xx);
	
	die(json_encode($z));
}

function send_close_connect_headers(){
	header("HTTP/1.1 444");
	header("Connection: close\r\n");
	header("Content-Encoding: none\r\n");
	ignore_user_abort(true);
	$z=array('_err'=>false,'_data'=>false,'command'=>'commit');
	echo json_encode($z,JSON_UNESCAPED_UNICODE);
}
/*--------------------------------------------------------------------------------------*/
class fso{
	public function __construct(){}
	public function _fix_path($path=false){
		if(!$path){return false;}
		$path=preg_replace('/[^[:print:]]/','',$path);
		$path=str_replace(array('/','\\'),DIRECTORY_SEPARATOR,$path);
		$z=explode(DIRECTORY_SEPARATOR,$path);
		foreach($z as $k => $v){if(empty($v)){unset($z[$k]);}}
		$path=implode(DIRECTORY_SEPARATOR,$z);
		return $path;
	}
		
	public function _split_string($wstring='',$split_last=null){
		if(empty($wstring)){return '';}
		$level=4;
		$psize=2;
		$split_last=(is_null($split_last))?false:(bool)$split_last;
		if($level<2 || !$psize){return $wstring;}
		$_e=explode(DIRECTORY_SEPARATOR,$wstring);
		$_we=array_pop($_e);
		$_we=preg_replace('/[^\w\d-_]/','',$_we);
		if(empty($_we)){return $wstring;}
		$r_len=$level*$psize;
		
		if(strlen($_we)<$r_len){$_we=sprintf('%0'.$r_len.'s',$_we);}
		
		$_r=array();
		if($split_last){$r_len-=$psize;}
		for($i=0,$c=$r_len;$i<$c;$i+=$psize){$_r[]=substr($_we,$i,$psize);}
		$_r[]=($split_last)?substr($_we,$r_len):$_we;
		if(!empty($_e)){$_r=array_merge($_e,$_r);}
		return implode(DIRECTORY_SEPARATOR,$_r);
	}
	
	public function _build_work_path($root,$home,$idx){
		$z=array($root,$home);
		$z[]=$idx;
		$path=implode(DIRECTORY_SEPARATOR,$z);
		$path=DIRECTORY_SEPARATOR.$this->_fix_path($path);
		return $path;
	}

	public function read($fn=false){
		if(!$fn){return false;}
		$x=@file_get_contents($fn);
		if(!$x){return false;}
		return $x;
	}
	
	public function write($fn=false,$str=''){
		if(!$fn){return false;}
		$dn=dirname($fn);
		if(!file_exists($dn)){
			if(!@mkdir($dn,0755,true)){return false;}
		}
		$fh=@fopen($fn,'w+');
		if(!$fh){return false;}
		fwrite($fh,$str);
		return fclose($fh);
	}	
}
?>