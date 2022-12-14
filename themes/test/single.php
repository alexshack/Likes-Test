<?php get_header(); ?>
    <div class="row">
      <main class="main">
      	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <h1 class="title"><?php the_title(); ?></h1>
			  
			  <?php the_content(); ?>
			  

        <?php get_template_part( 'pagination' ); ?>
      </main>
      <?php endwhile; endif; ?>
      <?php get_template_part( 'aside' ); ?>
    </div>
<?php get_footer(); ?>
