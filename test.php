<?php 
setlocale(LC_ALL,'pt_BR','pt_BR.utf-8');
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');

class AppStatus{

	public $status = 'done';
	public $paginas = 0;
	public $anuncios = 0;
	public static $mano = 'oi';
	function write(){
		try{
			$file = new SplFileObject('status.json','w+');
			$file->fwrite(json_encode($this));
		}catch(Exception $e){
			die($e->getMessage());
		}
	}
	function set_status($str)
	{
		$this->status = $str;
		$this->write();
	}
	function set_paginas($i)
	{
		$this->paginas = $i;
		$this->write();
	}
	function set_anuncios($i)
	{
		$this->paginas = $i;
		$this->write();
	}
}
$app = new AppStatus;
$app->set_status('carregando');