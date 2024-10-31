/**
 * MultiMailChimp client JavaScript library
 *
 * @author CreativeMinds (http://www.cminds.com)
 * @version 1.3
 * @copyright Copyright (c) 2012, CreativeMinds
 * @package MultiMailChimp/JavaScript
 */
jQuery(document).ready(function() {
    var MultiMailChimp = {
        /**
         * Init client library, bind to .mmc_button
         */
        init: function() {
            jQuery('#mmc_email_input').bind('keyup', function() {
                jQuery('.mmc_button').removeClass('mmc_unfollow mmc_pending').addClass('mmc_follow');
            });
            jQuery('#mmc_subscription_check').click(function(e) {
                e.preventDefault();
                jQuery('.mmc_button').removeClass('mmc_follow mmc_unfollow mmc_pending').addClass('mmc_loading');
                MultiMailChimp.checkSubscriptions(function(data) {
                    if (data.status=='update' && data.message) {
                        var list = data.message;
                        var row = {};
                        for (var i=0; i<list.length; i++) {
                            row = list[i];
                            var className = (row.isSubscribed)?'mmc_unfollow':'mmc_follow';
                            jQuery('.mmc_list_row[data-id='+row.id+'] .mmc_button').removeClass('mmc_follow mmc_pending mmc_loading mmc_unfollow').addClass(className);
                        }
                    } else {
                        jQuery('.mmc_button').removeClass('mmc_follow mmc_pending mmc_loading mmc_unfollow').addClass('mmc_follow');
                        if (data.status=='error') MultiMailChimp.showError(data.message);
                    }
                            
                });
            });
            jQuery('.mmc_button').click(function(e) {
                e.preventDefault();
                var button = jQuery(this);
                if (button.hasClass('mmc_follow') || button.hasClass('mmc_pending')) {
                    button.removeClass('mmc_follow mmc_pending').addClass('mmc_loading');
                    MultiMailChimp.subscribe(button.parents('.mmc_list_row').data('id'),
                        function(data) {
                            var className = 'mmc_unfollow';
                            if (data.status=='error') {
                                className = 'mmc_follow';
                                MultiMailChimp.showError(data.message);
                            } else if (data.status=='pending')
                                className = 'mmc_pending';
                            button.removeClass('mmc_follow mmc_loading').addClass(className);
                        });
                } else if (button.hasClass('mmc_unfollow') || button.hasClass('mmc_pending')) {
                    button.removeClass('mmc_unfollow mmc_pending').addClass('mmc_loading');
                    MultiMailChimp.unsubscribe(button.parents('.mmc_list_row').data('id'),
                        function(data) {
                            var className = 'mmc_follow';
                            if (data.status=='error') {
                                className = 'mmc_unfollow';
                                MultiMailChimp.showError(data.message);
                            }else if (data.status=='pending')
                                className = 'mmc_pending';
                            button.removeClass('mmc_unfollow mmc_loading').addClass(className);
                        });
                }
            });
        },
        checkSubscriptions: function(successFunc) {
            var email = '';
            var emailInput = jQuery('#mmc_email_input');
            if (emailInput.length==1) {
                email = emailInput.val();
            }
            jQuery.ajax({
                type: 'POST',
                data: {
                    mmc_ajax:1, 
                    mmc_action: 'checkSubscriptions',
                    mmc_email: email
                },
                success: successFunc
            });
        },
        /**
         * Subscribe user to a list via AJAX request
         * 
         * @param string id MailChimp List ID
         * @param callback successFunc
         */
        subscribe: function(id, successFunc) {
            var email = '';
            var emailInput = jQuery('#mmc_email_input');
            if (emailInput.length==1) {
                email = emailInput.val();
            }
            jQuery.ajax({
                type: 'POST',
                data: {
                    mmc_ajax:1, 
                    mmc_id: id, 
                    mmc_action: 'subscribe',
                    mmc_email: email
                },
                success: successFunc
            });
        },
        /**
         * Unsubscribe user from a list via AJAX request
         * 
         * @param string id MailChimp List ID
         * @param callback successFunc
         */
        unsubscribe: function(id, successFunc) {
            var email = '';
            var emailInput = jQuery('#mmc_email_input');
            if (emailInput.length==1) {
                email = emailInput.val();
            }
            jQuery.ajax({
                type: 'POST',
                data: {
                    mmc_ajax:1, 
                    mmc_id: id, 
                    mmc_action: 'unsubscribe',
                    mmc_email: email
                },
                success: successFunc
            });
        },
        /**
         * Show error message
         * 
         * @param string error message
         */
        showError: function(msg) {
            jQuery('#mmc_error').text(msg).fadeIn('fast').delay(5000).fadeOut('slow');
        }
    };
    MultiMailChimp.init();
});