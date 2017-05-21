<?php
	error_reporting(0);

	class Post {
		public $tipo;
		public $imagem;
		public $texto;
		public $visualizacoes;
		public $curtidas;
		public $comentarios;
		public $indc;
	}

	$videos = array();
	$fotos = array();

	$usertarget = 'meucabelonatural';

	$simpleurl = "https://www.instagram.com/".$usertarget;

	//returns a big old hunk of JSON from a non-private IG account page.
	function scrape_insta($source) {
		$insta_source = file_get_contents($source);
		$shards = explode('window._sharedData = ', $insta_source);
		$insta_json = explode(';</script>', $shards[1]); 
		$insta_array = json_decode($insta_json[0], TRUE);
		return $insta_array;
	}

	//Do the deed
	$results_array = scrape_insta($simpleurl);


	$indice = 0;
	$c_indice = 1;
	$c_fotos = 0;
	$c_videos = 0;

	//An example of where to go from there
	while ($indice < 12)
	{
		$latest_array = $results_array['entry_data']['ProfilePage'][0]['user']['media']['nodes'][$indice];
		
		/*
		echo '#'.$correcao.'<br/>';
		echo 'Tipo: ';
		if($latest_array['is_video']){ echo 'Video'.'<br/>';; }else{ echo 'Imagem'.'<br/>';; };
		echo '<a href="http://instagram.com/p/'.$latest_array['code'].'"><img src="'.$latest_array['display_src'].'" style="width: 80px;" ></a></br>';
		echo 'Views: '; if($latest_array['video_views']) { echo $latest_array['video_views']; }; 
		echo' - Likes: '.$latest_array['likes']['count'].' - Comentários: '.$latest_array['comments']['count'].'<br/><br/>'; 
		*/
		
		$post = new Post();

		if($latest_array['is_video']){ $post->tipo = 1; }else{ $post->tipo = 0; };

		$post->imagem = $latest_array['thumbnail_src'];
		$post->texto = $latest_array['caption'];

		if($latest_array['video_views']){ $post->visualizacoes = $latest_array['video_views']; }else{ $post->visualizacoes = 0; };

		$post->curtidas = $latest_array['likes']['count'];
		$post->comentarios = $latest_array['comments']['count'];
		$post->indc = $c_indice;
		$c_indice++;

		if($post->tipo == 1)
		{
			$videos[$c_videos] = $post;	
			$c_videos = $c_videos + 1;
		}
		else
		{
			$fotos[$c_fotos] = $post;
			$c_fotos = $c_fotos + 1;
		}

		if($indice == 11)
		{
			$keeper = $latest_array['id'];
		}

		$indice = $indice+1;
		$correcao = $correcao+1;

	}	

	$longurl = "https://www.instagram.com/".$usertarget."/?max_id=".$keeper;
	$results_array2 = scrape_insta($longurl);

	$indice = 0;

	//An example of where to go from there
	while ($indice < 12)
	{
		$latest_array = $results_array2['entry_data']['ProfilePage'][0]['user']['media']['nodes'][$indice];
		
		/*
		echo '#'.$correcao.'<br/>';
		echo 'Tipo: ';
		if($latest_array['is_video']){ echo 'Video'.'<br/>';; }else{ echo 'Imagem'.'<br/>';; };
		echo '<a href="http://instagram.com/p/'.$latest_array['code'].'"><img src="'.$latest_array['display_src'].'" style="width: 80px;" ></a></br>';
		echo 'Views: '; if($latest_array['video_views']) { echo $latest_array['video_views']; }; 
		echo' - Likes: '.$latest_array['likes']['count'].' - Comentários: '.$latest_array['comments']['count'].'<br/><br/>'; 
		*/
		
		$post = new Post();

		if($latest_array['is_video']){ $post->tipo = 1; }else{ $post->tipo = 0; };

		$post->imagem = $latest_array['thumbnail_src'];
		$post->texto = $latest_array['caption'];

		if($latest_array['video_views']){ $post->visualizacoes = $latest_array['video_views']; }else{ $post->visualizacoes = 0; };

		$post->curtidas = $latest_array['likes']['count'];
		$post->comentarios = $latest_array['comments']['count'];

		$post->indc = $c_indice;
		$c_indice++;

		if($post->tipo == 1)
		{
			$videos[$c_videos] = $post;	
			$c_videos = $c_videos + 1;
		}
		else
		{
			$fotos[$c_fotos] = $post;
			$c_fotos = $c_fotos + 1;
		}

		$indice = $indice+1;
		$correcao = $correcao+1;
	}


	echo '<h3>@'.$usertarget.' - Análise baseada nos últimos 24 posts</h3>' . '<br/>';
	echo 'Nome: ' . $results_array['entry_data']['ProfilePage'][0]['user']['full_name'] . '<br/>'; 
	echo 'ID: ' . $results_array['entry_data']['ProfilePage'][0]['user']['id'] . '<br/>';
	echo 'Bio: ' . $results_array['entry_data']['ProfilePage'][0]['user']['biography'] . '<br/>';
	echo 'Website: <a href="' . $results_array['entry_data']['ProfilePage'][0]['user']['external_url'] . '" target="_blank" />' . $results_array['entry_data']['ProfilePage'][0]['user']['external_url'] . '</a><br/>';
	echo 'Seguindo: ' . $results_array['entry_data']['ProfilePage'][0]['user']['follows']['count'] . '<br/>';
	echo 'Seguidores: ' . $results_array['entry_data']['ProfilePage'][0]['user']['followed_by']['count'] . '<br/>';
	echo '<br/>';
	
	$total = count($videos) + count($fotos);

	echo '<br/>';
	echo 'Total de vídeos: ' . count($videos) . ' (' . number_format((((count($videos) * 100)/$total)/100)*100, 2, '.', '') . '%)';
	echo '<br/>';
	echo 'Total de fotos: ' . count($fotos) . ' (' . number_format((((count($fotos) * 100)/$total)/100)*100, 2, '.', '') . '%)';
	echo '<br/>';
	echo '<br/>';

	
	// Vídeos - Ordenação de visualização
	usort(
    	$videos,
     	function( $a, $b ) {
        	if( $a->visualizacoes == $b->visualizacoes ) return 0;
         	return ( ( $a->visualizacoes > $b->visualizacoes ) ? -1 : 1 );
     	}
	);

	echo '<h3>Vídeos - Ordenados por visualização</h3>';

	for($item = 0; $item <= 2; $item++) {
		echo 'Indice: #' . $videos[$item]->indc . '<br/>';
		echo 'Preview: <br/><img src="' . $videos[$item]->imagem . '" style="width: 80px;" /><br/>';
		echo 'Texto: ' . $videos[$item]->texto . '<br/>';
		echo '<b>Visualizações: ' . $videos[$item]->visualizacoes . '</b><br/>';
		echo 'Curtidas: ' . $videos[$item]->curtidas . '<br/>';
		echo 'Comentários: ' . $videos[$item]->comentarios . '<br/>';
		echo '----------------------</br></br>';
	}

	// Vídeos - Ordenação por curtidas
	usort(
    	$videos,
     	function( $a, $b ) {
        	if( $a->curtidas == $b->curtidas ) return 0;
         	return ( ( $a->curtidas > $b->curtidas ) ? -1 : 1 );
     	}
	);

	echo '<h3>Vídeos - Ordenados por curtidas</h3>';

	for($item = 0; $item <= 2; $item++) {
		echo 'Indice: #' . $videos[$item]->indc . '<br/>';
		echo 'Preview: <br/><img src="' . $videos[$item]->imagem . '" style="width: 80px;" /><br/>';
		echo 'Texto: ' . $videos[$item]->texto . '<br/>';
		echo 'Visualizações: ' . $videos[$item]->visualizacoes . '<br/>';
		echo '<b>Curtidas: ' . $videos[$item]->curtidas . '</b><br/>';
		echo 'Comentários: ' . $videos[$item]->comentarios . '<br/>';
		echo '----------------------</br></br>';
	}

	// Vídeos - Ordenação por comentários
	usort(
    	$videos,
     	function( $a, $b ) {
        	if( $a->comentarios == $b->comentarios ) return 0;
         	return ( ( $a->comentarios > $b->comentarios ) ? -1 : 1 );
     	}
	);

	echo '<h3>Vídeos - Ordenados por comentários</h3>';

	for($item = 0; $item <= 2; $item++) {
		echo 'Indice: #' . $videos[$item]->indc . '<br/>';
		echo 'Preview: <br/><img src="' . $videos[$item]->imagem . '" style="width: 80px;" /><br/>';
		echo 'Texto: ' . $videos[$item]->texto . '<br/>';
		echo 'Visualizações: ' . $videos[$item]->visualizacoes . '<br/>';
		echo 'Curtidas: ' . $videos[$item]->curtidas . '<br/>';
		echo '<b>Comentários: ' . $videos[$item]->comentarios . '</b><br/>';
		echo '----------------------</br></br>';
	}

	// Fotos - Ordenação por curtidas
	usort(
    	$fotos,
     	function( $a, $b ) {
        	if( $a->curtidas == $b->curtidas ) return 0;
         	return ( ( $a->curtidas > $b->curtidas ) ? -1 : 1 );
     	}
	);

	echo '<h3>Fotos - Ordenadas por curtidas</h3>';

	for($item = 0; $item <= 2; $item++) {
		echo 'Indice: #' . $fotos[$item]->indc . '<br/>';
		echo 'Preview: <br/><img src="' . $fotos[$item]->imagem . '" style="width: 80px;" /><br/>';
		echo 'Texto: ' . $fotos[$item]->texto . '<br/>';
		echo '<b>Curtidas: ' . $fotos[$item]->curtidas . '</b><br/>';
		echo 'Comentários: ' . $fotos[$item]->comentarios . '<br/>';
		echo '----------------------</br></br>';
	}

	// Fotos - Ordenação por comentários
	usort(
    	$fotos,
     	function( $a, $b ) {
        	if( $a->comentarios == $b->comentarios ) return 0;
         	return ( ( $a->comentarios > $b->comentarios ) ? -1 : 1 );
     	}
	);

	echo '<h3>Fotos - Ordenadas por comentários</h3>';

	for($item = 0; $item <= 2; $item++) {
		echo 'Indice: #' . $fotos[$item]->indc . '<br/>';
		echo 'Preview: <br/><img src="' . $fotos[$item]->imagem . '" style="width: 80px;" /><br/>';
		echo 'Texto: ' . $fotos[$item]->texto . '<br/>';
		echo 'Curtidas: ' . $fotos[$item]->curtidas . '<br/>';
		echo '<b>Comentários: ' . $fotos[$item]->comentarios . '</b><br/>';
		echo '----------------------</br></br>';
	}

	$geral = array();
	$ix = 0;

	foreach ($videos as $vitem) {
		$geral[$ix] = $vitem;
		$ix++;
	}

	foreach ($fotos as $fitem) {
		$geral[$ix] = $fitem;
		$ix++;
	}

	// Geral - Ordenação por curtidas
	usort(
    	$geral,
     	function( $a, $b ) {
        	if( $a->curtidas == $b->curtidas ) return 0;
         	return ( ( $a->curtidas > $b->curtidas ) ? -1 : 1 );
     	}
	);

	echo '<h3>Geral - Ordenados por curtidas</h3>';

	for($item = 0; $item <= 2; $item++) {
		echo 'Indice: #' . $geral[$item]->indc . '<br/>';
		echo 'Tipo: ';
		if($geral[$item]->tipo == 1) { echo "Vídeo"; } else { echo "Imagem"; }; 
		echo '<br/>';
		echo 'Preview: <br/><img src="' . $geral[$item]->imagem . '" style="width: 80px;" /><br/>';
		echo 'Texto: ' . $geral[$item]->texto . '<br/>';
		echo 'Visualizações: ' . $geral[$item]->visualizacoes . '<br/>';
		echo '<b>Curtidas: ' . $geral[$item]->curtidas . '</b><br/>';
		echo 'Comentários: ' . $geral[$item]->comentarios . '<br/>';
		echo '----------------------</br></br>';
	}

	// Geral - Ordenação por comentários
	usort(
    	$geral,
     	function( $a, $b ) {
        	if( $a->comentarios == $b->comentarios ) return 0;
         	return ( ( $a->comentarios > $b->comentarios ) ? -1 : 1 );
     	}
	);

	echo '<h3>Geral - Ordenados por comentários</h3>';

	for($item = 0; $item <= 2; $item++) {
		echo 'Indice: #' . $geral[$item]->indc . '<br/>';
		echo 'Tipo: ';
		if($geral[$item]->tipo == 1) { echo "Vídeo"; } else { echo "Imagem"; };
		echo '<br/>';
		echo 'Preview: <br/><img src="' . $geral[$item]->imagem . '" style="width: 80px;" /><br/>';
		echo 'Texto: ' . $geral[$item]->texto . '<br/>';
		echo 'Visualizações: ' . $geral[$item]->visualizacoes . '<br/>';
		echo 'Curtidas: ' . $geral[$item]->curtidas . '<br/>';
		echo '<b>Comentários: ' . $geral[$item]->comentarios . '</b><br/>';
		echo '----------------------</br></br>';
	}

	die();


	//An example of where to go from there
	/*while ($indice < 12)
	{
		$latest_array = $results_array2['entry_data']['ProfilePage'][0]['user']['media']['nodes'][$indice];
		echo '#'.$correcao.'<br/>';
		echo 'Tipo: ';
		if($latest_array['is_video']){ echo 'Video'.'<br/>';; }else{ echo 'Imagem'.'<br/>';; };
		echo '<a href="http://instagram.com/p/'.$latest_array['code'].'"><img src="'.$latest_array['display_src'].'" style="width: 80px;" ></a></br>';
		echo 'Views: '; if($latest_array['video_views']) { echo $latest_array['video_views']; }; 
		echo' - Likes: '.$latest_array['likes']['count'].' - Comentários: '.$latest_array['comments']['count'].'<br/><br/>';
		$indice = $indice+1;
		$correcao = $correcao+1;
	}
	
	/* BAH! An Instagram site redesign in June 2015 broke quick retrieval of captions, locations and some other stuff. */
	//echo 'Taken at '.$latest_array['location']['name'].'<br/>';
	
	//Heck, lets compare it to a useful API, just for kicks.
	//echo '<img src="http://maps.googleapis.com/maps/api/staticmap?markers=color:red%7Clabel:X%7C'.$latest_array['location']['latitude'].','.$latest_array['location']['longitude'].'&zoom=13&size=300x150&sensor=false">';
?>