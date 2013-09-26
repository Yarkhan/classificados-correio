<?php 
setlocale(LC_ALL,'pt_BR','pt_BR.utf-8');
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Sao_Paulo');

class HFBS{

	public static function link_option_list(){
		$links = array();
		$files = glob('data/*.json');
		foreach($files as $name)
		{
			$timestamp = strtotime(substr($name,7,8));
			$link['label'] = strftime('%A',$timestamp).' '.date('d/m',$timestamp);
			$link['href'] = $name;
			$links[]= '<option value="'.$link['href'].'">'.$link['label'].'</a>';
		}
		$links = array_reverse($links);
		foreach($links as $link){
			print $link;
		}
	}
}

if(!empty($_GET['action']))
{
	if(method_exists('HFBS', $_GET['action']))
	{
		HFBS::$_GET['action']();
	}
}

