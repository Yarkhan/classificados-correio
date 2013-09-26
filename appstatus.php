<?php
class AppStatus{

	public $status = 'done';
	public $paginas = 0;
	public $anuncios = 0;
	function write(){
		try{
			$file = new SplFileObject('status.json','w+');
			$file->fwrite(json_encode($this));
		}catch(Exception $e){
			die($e->getMessage());
		}
	}
	function setStatus($str)
	{
		$this->status = $str;
		$this->write();
	}
	function setPaginas($i)
	{
		$this->paginas = $i;
		$this->write();
	}
	function setAnuncios($i)
	{
		$this->anuncios = $i;
		$this->write();
	}
}