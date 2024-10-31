=== Plugin Name ===
Name: CM Multi MailChimp List Manager
Contributors: CreativeMindsSolutions
Donate link: http://plugins.cminds.com/cm-multi-mailchimp-list-manager/
Tags: MailChimp, newsletter, email
Requires at least: 3.2
Tested up to: 3.5
Stable tag: 1.5.1

Allows users to subscribe/unsubscribe from multiple MailChimp lists.

== Description ==

Allows users to subscribe/unsubscribe from multiple MailChimp lists with Twitter-like user interface.

Admin can specify which MailChimp lists should be available for subscription and assign custom descriptions for them.

List can be shown using shortcode [mmc-display-lists] or widget.
The plugs support both loged-in and not loged-in users

Communication with MailChimp is based on MCAPI mini v1.3 downloaded from [here](http://apidocs.mailchimp.com/api/downloads/mailchimp-api-mini-class.zip)
The user interface is based on CSS3 stylesheet created by [Tim Hudson](https://github.com/timhudson/) [here](https://github.com/timhudson/Follow-Button).

**Demo** 

[View Widget demo](http://www.jumpstartcto.com) - Scroll down and look at the right site for the Subscribe for JumpStartCTO Blog

[View Page Demo](http://jumpstartcto.com/subscribe-to-mailing-list/)

**Note** 

Plugin is compatible with most modern browsers: Chrome (all versions), Firefox >=3.5, Safari >=1.3, Opera >=6, Internet Explorer >=8


**More About this Plugin**
	
You can find more information about CM Multi MailChimp List Manager at [CreativeMinds Website](http://plugins.cminds.com/cm-multi-mailchimp-list-manager/).

**More Plugins by CreativeMinds**

* [CM Super ToolTip Glossary](http://wordpress.org/extend/plugins/enhanced-tooltipglossary/) - Easily create Glossary, Encyclopedia or Dictionary of your terms and show tooltip in posts and pages while hovering. Many powerful features. 
* [CM Download manager](http://wordpress.org/extend/plugins/cm-download-manager) - Allow users to upload, manage, track and support documents or files in a directory listing structure for others to use and comment.
* [CM Answers](http://wordpress.org/extend/plugins/cm-answers/) - Allow users to post questions and answers (Q&A) in a stackoverflow style forum which is easy to use, customize and install. w Social integration.. 
* [CM Email Blacklist](http://wordpress.org/extend/plugins/cm-email-blacklist/) - Block users using blacklists domain from registering to your WordPress site.. 
* [CM Multi MailChimp List Manager](http://wordpress.org/extend/plugins/multi-mailchimp-list-manager/) - Allows users to subscribe/unsubscribe from multiple MailChimp lists. 
* [CM Invitation Codes](http://wordpress.org/extend/plugins/cm-invitation-codes/) - Allows more control over site registration by adding managed groups of invitation codes. 


== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set your MailChimp API Key and fetch available lists.
4. Choose which MailChimp Lists you want to enable for user's subscription.
5. Add user interface to your site by either using MultiMailChimp widget or shortcode [mmc-display-lists]

Note: You must have a call to wp_head() in your template in order for the JS plugin files to work properly.  If your theme does not support this you will need to link to these files manually in your theme (not recommended).

== Frequently Asked Questions ==

= Where can I find my API Key ? =

http://kb.mailchimp.com/article/where-can-i-find-my-api-key.

= Can non logged-in users subscribe or see the lists ? =

Currently the plugin supports only logged-in users. 

= How to show lists in page/posts

You can insert this plugin using a shortcode ([mmc-display-lists]) or as a widget.

== Screenshots ==

1. User interface of MultiMailChimp.
2. The options available for MultiMailChimp in the administration area.

== Changelog ==
= 1.5.1 =
* Update readme and plugin homepage

= 1.5 =
* Fixed wrong reference to javascript file
* Added prefix to MCAPI class name to avoid conflicts with other mailchimp plugins

= 1.4 =
* Minor fix in styling

= 1.3 = 
* Added support for non logged-in users

= 1.2 =
* Fixed display issues in admin pannel

= 1.1 =
* Added error messages when wrong API key has been given or there are no lists
* Fix for bug "plugin showing subscribed after being unsubscribed from MailChimp directly"
* Added "About" page and navigation menu

= 1.0 =
* Initial release

