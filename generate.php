<?php
require_once('appstatus.php');
$max_pages = 200;
$response_pages = array();
$page_vagas = array();
$ids = array();
$array_vagas = array();
$total_anuncios = 0;
$total_paginas = 0;
$app = new AppStatus;

$app->setStatus('Inicializando');
for($i=0;$i<$max_pages;$i++)
{	
	//pra pegar o dia atual
	$app->setStatus('Acessando pagina '.$i+1);
	$total_paginas++;
	$app->setPaginas($total_paginas);

	$ch = curl_init("http://www.classificadoscorreio.com.br/classificados/interna/lst_tipo_outros.jsp?tipoOrder=&codigoAnuncio=&dataAtualizacao=&codigoSessao=-1&isBuscaAvancada=&act=DEL&codigoPalavrasChaves=&paginaDestino=/classificados/interna/lst_tipo_outros.jsp&dia=0&codigoUsuario=-1&order=&isBusca=&id=&txtBuscaAvancada=&text=&validate=1&txtBusca=&cod_secao=61&codigoSecao=61&codigoFavoritos=&cod_secao_pai=61&paginaAtual=$i");

	//pra pegar o de domingo..
	// $ch = curl_init("http://www.classificadoscorreio.com.br/classificados/interna/lst_tipo_outros.jsp?tipoOrder=&codigoAnuncio=&dataAtualizacao=&codigoSessao=-1&isBuscaAvancada=&act=DEL&codigoPalavrasChaves=&paginaDestino=/classificados/interna/lst_tipo_outros.jsp&dia=1&codigoUsuario=-1&order=&isBusca=&id=&txtBuscaAvancada=&text=&validate=1&txtBusca=&cod_secao=61&codigoSecao=61&codigoFavoritos=&cod_secao_pai=61&paginaAtual=$i");
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_FOLLOWLOCATION,FALSE);
	$response_pages[$i] = curl_exec($ch);
	curl_close($ch);

	//Caso já tenhamos percorrido por todas as páginas do dia, parar o loop :)
	if(preg_match('/java.sql.SQLException/',$response_pages[$i]))
	{
		break;
	}
	
	//pega id's dos anuncios
	preg_match_all('/(?<=value=")[0-9]{8}(?=")/',$response_pages[$i],$ids);
	
	foreach ($ids[0] as $id) {
		$app->setStatus('Acessando anuncio '.$total_anuncios);
		$total_anuncios++;
		$app->setAnuncios($total_anuncios);

		$ch = curl_init("http://www.classificadoscorreio.com.br/classificados/outros/pop_outros.jsp?codigoAnuncio={$id}&codigoUsuario=-1&codigoSessao=-1&dataAtualizacao=");
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		$desc_response = curl_exec($ch);
		curl_close($ch);

		// pega descricao
		preg_match('#(?<=TxtNormalCinza">).*(?=</td>)#',$desc_response,$vaga_desc);
		$array_vagas[$id]['desc'] = htmlentities($vaga_desc[0],ENT_QUOTES);
		//pega o nivel
		preg_match('#(?<="PopUpTitulo3">).*(?=</td>)#',$desc_response,$vaga_nivel);
		$array_vagas[$id]['nivel'] = htmlentities($vaga_nivel[0],ENT_QUOTES);
		//pega o titulo
		if (preg_match('#(?<="RetrancaCinza">).*(?=<br>)#',$desc_response,$vaga_titulo) === 0) {

			preg_match('#(?<=Marca: <span class="RetrancaCinza").*(?=</span><br>)#',$desc_response,$vaga_titulo);
		}		
		$array_vagas[$id]['titulo'] = htmlentities($vaga_titulo[0],ENT_QUOTES);

	}

}

try{
	$file = new SplFileObject('data/'.date('Y-m-d').'.json','w+');
}catch(Exception $e)
{
	die($e->getMessage());
}

$file->bitesWritten = $file->fwrite(json_encode($array_vagas));
print "$file->bitesWritten bytes escritos no arquivo data/".date('Y-m-d').".json";
?>