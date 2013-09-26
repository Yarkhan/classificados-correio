function get_older_entries(){
	$.get('ajax.php?action=link_option_list')
		.done(function(data){
			$('#older_entries').html(data);
			load_entries($('#older_entries').val());
		});
}

function load_entries(path){
	json='';
	entries = Array();
	$.get(path).done(function(data){
		json = data;
		$.each(json,function(i,o){
			entries.push(o);
			$('#entries > tbody').html('');
		});

		for(i=0;i<entries.length;i++)
		{
			$('#entries > tbody').append(
				'<tr><td>'+entries[i]['nivel']+'</td><td>'+entries[i]['titulo']+'</td><td>'+entries[i]['desc']+'</td></tr>'
			);
		}
	});
}
function generate(){
	status = setInterval(function(){
		$.get('status.json').done(function(data){
			$('#status').html(data.status);
		$('#generate').animate({
			opacity: 0.5,
		},200);
		$('#generate').animate({
			opacity: 1,
		},200);		
		});
	},500);
	$.get('generate.php').done(function(){
		get_older_entries();
		load_entries($('#older_entries').val());
		clearInterval(status);
		$('#status').html('');
		$('#generate').html('Gerar');
	});

}
$('#older_entries').on('change',function(e){
	load_entries($('#older_entries').val());
});

$('#find').keyup(function(e){
	$('#entries').tableSearch($(this).val());
});
$('#generate').click(function(){
	generate();
	$(this).html('Carregando..');
});
get_older_entries();
load_entries($('#older_entries').val());