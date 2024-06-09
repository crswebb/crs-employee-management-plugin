<div class="employee">
    <h3>
        <?php the_title(); ?>
    </h3>
    <?php if (has_post_thumbnail()): ?>
        <div class="employee-thumbnail">
            <?php the_post_thumbnail('medium'); ?>
        </div>
    <?php endif; ?>
    <div class="employee-details">
        <p class="employee-title">
            <?php echo get_post_meta(get_the_ID(), 'employee_title', true); ?>
        </p>
        <p class="employee-email">
            <a href="mailto:<?php echo get_post_meta(get_the_ID(), 'employee_email', true); ?>"><?php echo get_post_meta(get_the_ID(), 'employee_email', true); ?></a>
        </p>
        <p class="employee-phone">
            <?php echo get_post_meta(get_the_ID(), 'employee_phone', true); ?>
        </p>
        <p class="employee-description">
            <?php the_content(); ?>
        </p>
    </div>
</div>
