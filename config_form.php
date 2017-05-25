
<div id="IpRestrictConfigForm">
    <div class="field">
        <div class="two columns alpha">
            <?php echo get_view()->formLabel('restriction_text', __('Restriction Text')); ?>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">
                <?php echo __('Warning text advising user that the content is restrict to certain place.'); ?>
            </p>
            <?php $attr['rows'] = 5; echo get_view()->formTextarea('restriction_text', get_option('ip_restrict_message'), $attr); ?>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <?php echo get_view()->formLabel('restriction_text', __('IP Ranges Availables')); ?>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">
                <?php echo __("Follow the format to define ranges availables to choose on registers:<br> <font face=courier>[alias]:[lowIP-highIP],[oneIP];[otheralias]:[otherIP]</font>"); ?>
            </p>
            <font face=courier><?php echo get_view()->formTextarea('ip_ranges', get_option('ip_ranges'), array('rows'=>7)); ?></font>
        </div>
    </div>
</div>
