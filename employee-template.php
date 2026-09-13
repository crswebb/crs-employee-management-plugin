<?php defined('ABSPATH') || exit; ?>
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
            <?php echo esc_html(get_post_meta(get_the_ID(), 'crs_employee_title', true)); ?>
        </p>
        <p class="employee-email">
            <?php $employee_email = get_post_meta(get_the_ID(), 'crs_employee_email', true); ?>
            <a href="mailto:<?php echo esc_attr(antispambot($employee_email)); ?>"><?php echo esc_html(antispambot($employee_email)); ?></a>
        </p>
        <p class="employee-phone">
            <?php echo esc_html(get_post_meta(get_the_ID(), 'crs_employee_phone', true)); ?>
        </p>
        <p class="employee-description">
            <?php the_content(); ?>
        </p>
    </div>
</div>
