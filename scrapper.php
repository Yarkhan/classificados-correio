<?php
/* Número máximo de páginas à procurar. Diminuir isto para 2 para fazer testes
 * e evitar dezenas de requests no site dos correios.*/
$max_pages = 200; 

$response_pages = array(); //container para cada request de páginas
$page_vagas = array();
$ids = array(); //Id de cada vaga, em cada página (acho que são 10 por página)
$array_vagas = array();
$total_anuncios = 0;
$total_download =0; //total em bytes baixados em cada request

for($i=0;$i<$max_pages;$i++)
{
    $ch = curl_init("http://www.classificadoscorreio.com.br/classificados/interna/lst_tipo_outros.jsp?tipoOrder=&codigoAnuncio=&dataAtualizacao=&codigoSessao=-1&isBuscaAvancada=&act=DEL&codigoPalavrasChaves=&paginaDestino=/classificados/interna/lst_tipo_outros.jsp&dia=0&codigoUsuario=-1&order=&isBusca=&id=&txtBuscaAvancada=&text=&validate=1&txtBusca=&cod_secao=61&codigoSecao=61&codigoFavoritos=&cod_secao_pai=61&paginaAtual=$i");
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,FALSE);
    $response_pages[$i] = curl_exec($ch);
    $total_download += curl_getinfo($ch,CURLINFO_SIZE_DOWNLOAD);
    curl_close($ch);

    // Caso já tenhamos percorrido por todas as páginas do dia, parar o loop :)
    if(preg_match('/java.sql.SQLException/',$response_pages[$i]))
    {
        break;
    }
    
    // Armazenar o id de cada vaga da página
    preg_match_all('/(?<=value=")[0-9]{8}(?=")/',$response_pages[$i],$ids);
    
    // Agora que já temos o id de cada vaga, vamos acessar cada maldito pop-up irritante
    // e pegar informações  sobre as vagas. 
    foreach ($ids[0] as $id) {
        $total_anuncios++;
        $ch = curl_init("http://www.classificadoscorreio.com.br/classificados/outros/pop_outros.jsp?codigoAnuncio={$id}&codigoUsuario=-1&codigoSessao=-1&dataAtualizacao=");
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $desc_response = curl_exec($ch);
        curl_close($ch);

        // pega descricao
        preg_match('#(?<=TxtNormalCinza">).*(?=</td>)#',$desc_response,$vaga_desc);
        $array_vagas[$id]['desc'] = $vaga_desc[0];

        //pega o nivel
        preg_match('#(?<="PopUpTitulo3">).*(?=</td>)#',$desc_response,$vaga_nivel);
        $array_vagas[$id]['nivel'] = $vaga_nivel[0];

        /* Pega o título
         * Uma gambiarrinha neste if: As vezes eles esquecem de escrever o título da vaga
         * ou ela aparece dentro deste campo "Marca". Sabe-se lá o porque.
        */
        if (preg_match('#(?<="RetrancaCinza">).*(?=<br>)#',$desc_response,$vaga_titulo) === 0) {

            preg_match('#(?<=Marca: <span class="RetrancaCinza").*(?=</span><br>)#',$desc_response,$vaga_titulo);
        }       
        $array_vagas[$id]['titulo'] = $vaga_titulo[0];
    }       
}
/* E terminamos. Se tudo ocorreu bem, temos uma array no seguinte esquema:
 * $array_vagas => Array
 *      id => Array
 *          descricao 
 *          titulo
 *          nivel   
 */
ob_start();
?>
<!doctype html>
<html lang="pt-br">
<head>
    <title>Classificados de Empregos CorreioWeb (Gentilmente organizado por Yarkhan)</title>
    <script type="text/javascript" src="jquery.js"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Classificados do CorreioWeb - <?=date('d/m/y')?></h1>
    <h2>Gentilmente organizado por <a href="https://github.com/Yarkhan/" id="about-me">Yarkhan</a></h2>
    <div id="about">
        <p><em>Dicas:</em></p>
        <ul>
            <li>Procure pelo nome da vaga em <em>Pesquisar</em>. Super pr&aacute;tico.</li>
            <li>Voc&ecirc; pode ordenar os resultados de A-Z ou por n&iacute;vel, clicando em 
                <em>N&iacute;vel Requerido</em>, <em>T&iacute;tulo</em> e ou <em>Descri&ccedil;&atilde;o</em></li>
        </ul>
        <p><em>Stats:</em></p>
        <ul>
            <li>Total Baixado: <?=$total_download?></li>
        </ul>
    </div>
    <div>
        <span>Pesquisar Vaga</span>
        <input type="text" id="busca">
    </div>
    <table id="vagas">
    <thead>
        <th data-sort="string"><a href="#">N&iacute;vel Requerido</a></th>
        <th data-sort="string"><a href="#">T&iacute;tulo</a></th>
        <th data-sort="string"><a href="#">Descri&ccedil;&atilde;o</a></th>
    </thead>
    <tbody>
    <tbody>
        <?php foreach ($array_vagas as $vaga): ?>
        <tr>
            <td><?=$vaga['nivel']?></td>
            <td><?=$vaga['titulo']?></td>
            <td><?=$vaga['desc']?></td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
<script>
    $('#about-me').click(function(){$('#about').fadeToggle('slow')});
    $.extend($.expr[":"], {
        "icontains": function(elem, i, match, array) {
        return (elem.textContent || elem.innerText || "").toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
        }
    }); 
    $('#busca').keyup(function(){
      var searchterm = $(this).val();
      if(searchterm.length > 0) {
       var match = $('#vagas > tbody > tr:icontains("' + searchterm + '")');
       var nomatch = $('#vagas > tbody > tr:not(:icontains("' + searchterm + '"))');
       match.fadeIn();
       nomatch.fadeOut();
      } else {
       $('#vagas > tbody > tr').fadeIn();
       $('#vagas > tbody > tr').fadeIn();
      }
    }); 
</script>
</body>
</html>
<?php ob_end_flush(); ?>
