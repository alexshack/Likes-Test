<?php


$posts_pagination = get_the_posts_pagination(
    array(
      'prev_text' => 'Назад',
      'next_text' => 'Вперед',
      'screen_reader_text' => '',
    )
);

if ( strpos( $posts_pagination, 'prev page-numbers' ) === false ) {
	$posts_pagination = str_replace( '<div class="nav-links">', '<div class="nav-links"><span class="prev page-numbers placeholder" aria-hidden="true">Назад</span>', $posts_pagination );
}

if ( strpos( $posts_pagination, 'next page-numbers' ) === false ) {
	$posts_pagination = str_replace( '</div>', '<span class="next page-numbers placeholder" aria-hidden="true">Вперед</span></div>', $posts_pagination );
}

if ( $posts_pagination ) { 
		 echo $posts_pagination;

}
?>
