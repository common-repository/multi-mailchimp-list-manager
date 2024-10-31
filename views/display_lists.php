<?php
/**
 * CM Multi MailChimp "Display Lists" user view
 *
 * @author CreativeMinds (http://plugins.cminds.com)
 * @version 1.3
 * @copyright Copyright (c) 2012, CreativeMinds
 * @package MultiMailChimp/Views
 */
?>
<div id="mmc_error" style="display:none"></div>
<?php if (!is_user_logged_in()): ?>
    <div class="mmc_notloggedin">
        <input type="text" placeholder="Your E-mail" id="mmc_email_input"/>
        <button id="mmc_subscription_check">Check</button>
    </div>
<?php endif; ?>
<ul class="mmc_list">
    <?php
    foreach ($subscriptionList as $row):
        ?>
        <li class="mmc_list_row" data-id="<?php echo $row['id']; ?>">
            <a class="mmc_button mmc_<?php echo ($row['isSubscribed']) ? 'unfollow' : 'follow'; ?>"></a>
            <div class="mmc_list_label">
                <span class="mmc_list_name"><?php echo $row['name']; ?></span>
                <span class="mmc_list_description"><?php echo $row['description']; ?></span>
            </div>
        </li>
        <?php
    endforeach;
    ?>
</ul>
     

