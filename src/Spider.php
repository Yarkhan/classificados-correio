<?php
namespace Yarkhan\Classificados; 

use Yarkhan\Classificados\Job;
use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
use \pQuery;
class Spider{

	public $links = [];
	public $jobs = [];
	public $jobUrl = "http://www.classificadoscb.com.br/anuncio/empregos-e-formacao-profissional/";
	private $log = null;

	function __construct(){
		$this->log = new Logger("job log");
		$this->log->pushHandler(new ErrorLogHandler,Logger::DEBUG);
		file_put_contents("data/".date("Y-m-d").'.json', 'data');
	}
	function getPageLinks($page){
		$result = null;
		$reg = "|$this->jobUrl(\d+)|";
		$this->log->info("Acessando links",["página",$page]);
		$data = file_get_contents("http://www.classificadoscb.com.br/anuncio/empregos-e-formacao-profissional/secao,Subse%C3%A7%C3%A3o%20%20N%C3%ADvel%20B%C3%A1sico/secao,Subse%C3%A7%C3%A3o%20%20N%C3%ADvel%20M%C3%A9dio/subcategoria,Oferta%20de%20Emprego/ordenar-por,insercao-desc/apartir-de,".$page*20);
		preg_match_all($reg,$data,$result);
		$result = array_unique($result[0]);

		return $result;
	}

	function getTodayLinks(){
		$page = 0;
		$result_count = 0;
		while($links = $this->getPageLinks($page)){

			$page++;
			$this->links = array_merge($this->links,$links);
		}
		$this->log->info(["Total de anúncios: ", count($this->links)]);
	}

	function getDescription($link){
		$result = null;
		$data = file_get_contents($link);
		$dom = pQuery::parseStr($data);
		return $dom->query('.description__bx p')->html();
	}


	function fetchJobs(){
		$result = "";
		$this->getTodayLinks();
		for($i=0;$i<count($this->links);$i++){
			$this->log->info("Acessando anúncio nº: $i");
			$link = $this->links[$i];
			if($description = $this->getDescription($link)){
				$job = new Job;
				$job->id = str_replace($this->jobUrl,'',$link);
				$job->link = $link;
				$job->description = $description;
				$this->jobs[]=$job;
			}
		}
		$this->log->info("salvando dados");
		file_put_contents("data/".date("Y-m-d").'.json', json_encode($this->jobs));
	}
}