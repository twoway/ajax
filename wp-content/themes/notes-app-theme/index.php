<?php 

    get_header();

?>
        <section role="main" class="notes">
            <ul>
                
                
                <?php if(have_posts()) : while(have_posts()) : the_post(); ?>
                    <li class="note" id="post-<?php the_ID(); ?>">
                        <a class="delete-post action" href="#delete-post" data-modal="" data-action="delete-post" data-id="<?php the_ID(); ?>">delete</a>
                        <input type="text" value="<?php the_title(); ?>" maxlength="140">
                        <a class="update-post action" href="#update-post" data-modal="" data-action="update-post", data-id="<?php the_ID(); ?>">update</a>
                    </li>
                <?php endwhile; endif; ?>

                <li class="new-post">
                    <a class="add-post action" href="#new-post" data-modal="" data-action="new-post">new post</a>
                    <input type="text" value="New note..." maxlength="140">
                </li>
            </ul>
        </section>

        <?php get_footer(); ?>