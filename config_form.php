
<div id="IpRestrictConfigForm">
    <div class="field">
        <div class="two columns alpha">
            <?php echo get_view()->formLabel('restriction_text', __('Restriction Text')); ?>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">
                <?php echo __('Warning text advising user that the content is restrict to certain place.'); ?>
            </p>
            <?php echo get_view()->formTextarea('restriction_text', get_option('ip_restrict_message')); ?>
        </div>
    </div>
</div>
