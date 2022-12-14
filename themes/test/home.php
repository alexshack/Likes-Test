<?php get_header(); ?>
    <div class="row">
      <main class="main">
        <h1 class="title">Статьи</h1>
			  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			  <?php get_template_part( 'entry' ); ?>
			  <?php endwhile; endif; ?>

        <?php get_template_part( 'pagination' ); ?>
      </main>
      <?php get_template_part( 'aside' ); ?>
    </div>
<?php get_footer(); ?>