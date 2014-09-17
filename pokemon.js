$(function() {

var compteur = 0;
var ajax_url = $('body').data('ajax');

		$( "#fight" ).hide();

		$('.attaquant').click(function(ev){
				ev.preventDefault();
				compteur++;
				console.log(compteur);
				$( "#arene" ).hide();
				var pkmn = $(ev.target).parents('[data-type="pokemon"]');
				$('#arene').append('<div class="pokemon" data-id="'+pkmn.data('id')+'"><h2>'+pkmn.data('nom')+'</h2></div>');
				if(compteur ==2) {
					$('#arene').show();
					$('#cartes').remove();
					$('#more').remove();
					$('#arene').addClass("combat");
					$('.pokemon').addClass("attaque");
					var ids = [];
					$('#arene > div').each(function(i) {
						ids.push($(this).data('id'));
					});
					$.get('/combat/debut', {attaquant: ids[0],
											defenseur: ids[1]}, function(data) {
						$('#fight').show();
					});
					$('#fight').click(function(ev){
						$.get('/combat/round', {attaquant: ids[0],
											defenseur: ids[1]}, function(data) {

												console.log('combat');
						});
					});
				}
		});
});