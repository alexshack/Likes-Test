<?php 
  $theme_link = get_template_directory_uri(); 
  $likes = get_post_likes(get_the_id());
  $my_likes = is_my_post_like(get_the_id());
?> 

         <article class="article" id="post-<?php the_ID(); ?>">
          <div class="article__img">
            <?php if ( has_post_thumbnail() ) : ?>
              <?php the_post_thumbnail( 'full', array( 'class' => 'article__thumb' ) ); ?>
            <?php endif; ?>
          </div>
          <div class="article__content">
            <h2 class="article__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <p class="article__description"><?= get_the_excerpt(); ?></p>
            <div class="article__footer">
              <div class="article__author">
                <span>Автор: </span>
                <?php the_author_posts_link(); ?>
              </div>
              <div class="article__likes" data-likes="<?= $my_likes ?>" data-post="<?php the_id(); ?>">
                <span class="like__btn like__btn__plus" >
                  <svg><use xlink:href="<?= $theme_link ?>/assets/img/icons.svg#plus"></use></svg>
                </span>
                <span class="like__count <?= $likes[1] ?>"><?= $likes[0] ?></span>
                <span class="like__btn like__btn__minus">
                  <svg><use xlink:href="<?= $theme_link ?>/assets/img/icons.svg#minus"></use></svg>
                </span>
              </div>
            </div>
          </div>          
        </article>