<?php
/**
 * CM Multi MailChimp processors
 * @package MultiMailChimp/Library
 */
require_once MMC_PATH . '/lib/MCAPI.class.php';

/**
 * MailChimp processor singleton
 *
 * @author CreativeMinds (http://plugins.cminds.com)
 * @version 1.2
 * @copyright Copyright (c) 2012, CreativeMinds
 * @package MultiMailChimp/Library
 */
class MultiMailChimp {

    /**
     * @var string $_api_key MailChimp API Key
     */
    protected $_api_key = '';

    /**
     * @var array $_lists_ids Enabled lists for subscription
     */
    protected $_lists_ids = array();

    /**
     * @var array $_all_lists All lists for the account
     */
    protected $_all_lists = array();

    /**
     * @var array $_list_descriptions User-defined descriptions for lists
     */
    protected $_list_descriptions = array();

    /**
     * @var CM_MCAPI $_api MailChimp API Handler
     */
    protected $_api = null;

    /**
     * @var MultiMailChimp $_instance Singleton instance
     * @static
     */
    protected static $_instance = null;

    /**
     * WP option key for MailChimp API Key
     */
    const OPTION_API_KEY = 'mmc_api_key';
    /**
     * WP option key for enabled lists
     */
    const OPTION_LISTS_IDS = 'mmc_lists_ids';
    /**
     * WP option key for lists descriptions
     */
    const OPTION_LIST_DESCRIPTIONS = 'mmc_list_descriptions';
    /**
     * WP option key for all lists
     */
    const OPTION_ALL_LISTS = 'mmc_all_lists';

    /**
     * MailChimp subscription status: not exists
     */
    const STATUS_NOTEXISTS = 'notexists';
    /**
     * MailChimp subscription status: pending
     */
    const STATUS_PENDING = 'pending';
    /**
     * MailChimp subscription status: unsubscribed
     */
    const STATUS_UNSUBSCRIBED = 'unsubscribed';
    /**
     * MailChimp subscription status: subscribed
     */
    const STATUS_SUBSCRIBED = 'subscribed';
    /**
     * Admin menu slug
     */
    const MENU_OPTION = 'multi-mailchimp';
    /**
     * About panel slug
     */
    const MENU_OPTION_ABOUT = 'multi-mailchimp-about';

    /**
     * Init plugin
     */
    public static function init() {
        add_action('admin_menu', array(get_class(), 'registerOptionsPage'));
        add_shortcode('mmc-display-lists', array(get_class(), 'shortcodeDisplayLists'));
        add_action('widgets_init', create_function('', 'return register_widget("MultiMailChimpWidget");'));
        add_action('mmc_admin_page', array(get_class(), 'showAdminPage'), 1, 1);
        if (!is_admin()) {
            add_action('init', array(get_class(), 'processSubscribeAjax'));
            wp_register_style('multimailchimp_style', MMC_URL . '/views/style.css');
            wp_enqueue_style('multimailchimp_style');
            wp_register_script('mmc_subscribe', MMC_URL . '/views/js/subscribe.js', array('jquery'));
            wp_enqueue_script('mmc_subscribe');
        }
    }

    /**
     * Get singleton instance
     * 
     * @static
     * @return MultiMailChimp
     */
    public static function getInstance() {
        if (empty(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->getOptions();
    }

    /**
     * Get MailChimp API handler
     */
    public function getAPI() {
        return $this->_api;
    }

    /**
     * Subscribe email to list
     * 
     * @static
     * @param string $email E-mail to be subscribed
     * @param string $list MailChimp List ID
     * @return boolean 
     */
    public static function subscribeMail($email, $list, $data = array()) {
        $mc = self::getInstance();
        $subscribed = false;
        $api = $mc->getAPI();
        if (!empty($api)) {
            if ($email && is_email($email)) {
                $subscribed = $api->listSubscribe($list, $email, $data, 'html', false, true);
            }
        }
        return $subscribed;
    }

    /**
     * Unsubscribe e-mail from list
     * 
     * @static
     * @param string $email E-mail to be unsubscribed
     * @param string $list MailChimp List ID
     * @return boolean 
     */
    public static function unsubscribeMail($email, $list) {
        $mc = self::getInstance();
        $unsubscribed = false;
        if (!empty($mc->_api)) {
            if ($email && is_email($email)) {
                $unsubscribed = $mc->_api->listUnsubscribe($list, $email, true, false, false);
            }
        }
        return $unsubscribed;
    }

    /**
     * Get subscription status
     * 
     * @static
     * @param string $email E-mail
     * @param string $list MailChimp List ID
     * @return string Status name 
     */
    public static function getSubscriptionStatus($email, $list) {
        $mc = self::getInstance();
        $subscribed = self::STATUS_NOTEXISTS;
        $api = $mc->getAPI();
        if (!empty($api)) {
            if ($email && is_email($email)) {
                $response = $api->listMemberInfo($list, array($email));
                if (!$api->errorCode && $response['success'] == 1) {
                    $subscribed = $response['data'][0]['status'];
                }
            }
        }
        return $subscribed;
    }

    /**
     * Checks whether e-mail is subscribed to a list
     * 
     * @static
     * @param string $email E-mail
     * @param string $list MailChimp List ID
     * @return boolean 
     */
    public static function isEmailSubscribed($email, $list) {
        return self::getSubscriptionStatus($email, $list) == self::STATUS_SUBSCRIBED;
    }

    /**
     * Get options for plugin
     * 
     * @return array
     */
    public function getOptions() {
        $this->_api_key = get_option(self::OPTION_API_KEY, $this->_api_key);
        $this->_lists_ids = get_option(self::OPTION_LISTS_IDS, $this->_lists_ids);
        $this->_all_lists = get_option(self::OPTION_ALL_LISTS, $this->_all_lists);
        $this->_list_descriptions = get_option(self::OPTION_LIST_DESCRIPTIONS, $this->_list_descriptions);
        if (!empty($this->_api_key)) {
            $this->_api = new CM_MCAPI($this->_api_key);
        }
        return array(self::OPTION_API_KEY => $this->_api_key, self::OPTION_LISTS_IDS => $this->_lists_ids, self::OPTION_LIST_DESCRIPTIONS => $this->_list_descriptions);
    }

    /**
     * Get list of available MailChimp lists for API Key
     * 
     * @param string $apiKey MailChimp API Key
     * @param boolean $forceReload Force reloading lists from MailChimp instead of read cached ones
     * @return array List of List IDs 
     */
    public function getAvailableLists($apiKey = '', $forceReload = false) {
        if (!empty($apiKey)) {
            $this->_api = new CM_MCAPI($apiKey);
        }
        if (empty($apiKey) && !empty($this->_all_lists) && !$forceReload) {
            return $this->_all_lists;
        }
        if (empty($this->_api)) {
            throw new Exception('API Key is missing');
        }

        $lists = $this->_api->lists(array(), 0, 100);
        if ($lists === false) {
            throw new Exception('API Key is not valid');
        }
        if ($lists['total'] == 0)
            throw new Exception('There are no lists for this API Key');
        $lists = $lists['data'];
        $listArr = array();
        foreach ($lists as $list) {
            $listArr[$list['id']] = $list['name'];
        }
        $this->_all_lists = $listArr;
        update_option(self::OPTION_ALL_LISTS, $this->_all_lists);
        return $listArr;
    }

    /**
     * Save plugin options
     * 
     * @param array $options 
     */
    public function saveOptions($options) {
        update_option(self::OPTION_API_KEY, $options[self::OPTION_API_KEY]);
        update_option(self::OPTION_LISTS_IDS, $options[self::OPTION_LISTS_IDS]);
        update_option(self::OPTION_LIST_DESCRIPTIONS, $options[self::OPTION_LIST_DESCRIPTIONS]);
    }

    /**
     * Add new options page in admin panel
     * 
     * @static
     */
    public static function registerOptionsPage() {
        self::processAdminAjax();
        add_menu_page('Settings', 'CM Multi MailChimp', 'manage_options', self::MENU_OPTION, array(get_class(), 'adminMenu'));
        add_submenu_page(self::MENU_OPTION, 'About', 'About', 'manage_options', self::MENU_OPTION_ABOUT, array(get_class(), 'displayAboutPage'));
    }

    /**
     * Render new page in admin panel
     * 
     * @static
     */
    public static function adminMenu() {
        wp_register_script('mmc_admin', MMC_URL . '/views/js/admin-1.3.js', array('jquery'));
        wp_enqueue_script('mmc_admin');
        $mailChimp = self::getInstance();
        if (isset($_POST['options'])) {
            check_admin_referer('multi-mailchimp-config');
            $mailChimp->saveOptions($_POST['options']);
        }


        $options = $mailChimp->getOptions();
        try {
            $lists = $mailChimp->getAvailableLists('', true);
        } catch (Exception $e) {
            
        }
        $descriptions = $options[self::OPTION_LIST_DESCRIPTIONS];
        ob_start();
        require MMC_PATH . '/views/admin_page.php';
        $content = ob_get_contents();
        ob_end_clean();
        do_action('mmc_admin_page', $content);
    }

    /**
     * Display available lists + subscribe controls to a user
     * 
     * @static
     */
    public static function displayLists($_email = '', $widget = false) {
        $mc = self::getInstance();
        if ($mc->getAPI() != null) {
            $listNames = $mc->getAvailableLists();
            $descriptions = $mc->getListDescriptions();
            $allowedLists = $mc->getListsIDs();
            $email = is_user_logged_in() ? wp_get_current_user()->user_email : $_email;
            foreach ($allowedLists as $listId) {
                $listName = $listNames[$listId];
                $description = isset($descriptions[$listId]) ? $descriptions[$listId] : '';
                $isSubscribed = !empty($email) ? self::isEmailSubscribed($email, $listId) : false;
                $subscriptionList[] = array('id' => $listId, 'name' => $listName, 'description' => $description, 'isSubscribed' => $isSubscribed);
            }
            if (empty($_email)) {
                require MMC_PATH . '/views/display_lists.php'; 
                if (!$widget):
                ?>
                   <!--// By leaving following snippet in the code, you're expressing your gratitude to creators of this plugin. Thank You! //-->
<span class="cm_poweredby"><a href="http://plugins.cminds.com/" target="_new" class="cm_poweredbylink">CreativeMinds WordPress Plugin</a><a href="http://plugins.cminds.com/cm-multi-mailchimp-list-manager/" target="_new" class="cm_poweredbylink">Multi MailChimp</a></span>
<?php endif;
            }
            else
                return $subscriptionList;
        }
    }

    /**
     * Display lists from shortcode [mmc_display_lists]
     * 
     * @param array $atts
     * @param string $content
     * @param string $code
     */
    public static function shortcodeDisplayLists($atts, $content = null, $code = '') {
        if (is_feed())
            return '';
        self::displayLists();
    }

    /**
     * Process ajax "fetch available lists" in admin panel
     */
    public static function processAdminAjax() {
        if (current_user_can('manage_options') && isset($_POST['ajax']) && isset($_POST['mmc_api_key'])) {
            $apiKey = $_POST['mmc_api_key'];
            header('Content-type: application/json');
            $output = array('success' => 1, 'data' => array());
            try {
                $lists = self::getInstance()->getAvailableLists($apiKey);

                $descriptions = self::getInstance()->getListDescriptions();
                $output = array('success' => 1, 'data' => array());
                foreach ($lists as $key => $name) {
                    $descr = isset($descriptions[$key]) ? $descriptions[$key] : '';
                    $output['data'][$key] = array('name' => $name, 'description' => $descr);
                }
            } catch (Exception $e) {
                $output['success'] = 0;
                $output['message'] = $e->getMessage();
            }
            echo json_encode($output);
            exit;
        }
    }

    /**
     * Process ajax "subscribe/unsubscribe" on user side
     */
    public static function processSubscribeAjax() {
        if (isset($_POST['mmc_ajax']) && isset($_POST['mmc_action'])) {
            $result = false;
            $msg = $firstName = $lastName = '';
            if (is_user_logged_in()) {
                $user = wp_get_current_user();
                $email = $user->user_email;
                $firstName = $user->first_name;
                $lastName = $user->last_name;
            } else {
                $email = $_POST['mmc_email'];
                if (empty($email)) {
                    $result = 'error';
                    $msg = 'E-mail was not provided';
                } elseif (!is_email($email)) {
                    $result = 'error';
                    $msg = 'E-mail is not in valid format';
                }
            }
            if (!$result)
                switch ($_POST['mmc_action']) {
                    case 'checkSubscriptions':
                        $msg = self::displayLists($email);
                        $result = 'update';
                        break;
                    case 'subscribe':
                        $data = array(
                            'FNAME' => $firstName,
                            'LNAME' => $lastName
                        );
                        self::subscribeMail($email, $_POST['mmc_id'], $data);
                        $result = self::getSubscriptionStatus($email, $_POST['mmc_id']);
                        break;
                    case 'unsubscribe':
                        self::unsubscribeMail($email, $_POST['mmc_id']);
                        $result = self::getSubscriptionStatus($email, $_POST['mmc_id']);
                        break;
                }
            header('Content-type: application/json');
            echo json_encode(array('status' => $result, 'message' => $msg));
            exit;
        }
    }

    /**
     * Get enabled lists IDs
     * 
     * @return array MailChimp Lists IDs
     */
    public function getListsIDs() {
        return $this->_lists_ids;
    }

    /**
     * Get list descriptions
     * 
     * @return array MailChimp List Descriptions
     */
    public function getListDescriptions() {
        return $this->_list_descriptions;
    }

    public static function showAdminPage($content) {
        require(MMC_PATH . '/views/admin_template.php');
    }

    public static function displayAboutPage() {
        ob_start();
        require(MMC_PATH . '/views/admin_about.php');
        $content = ob_get_contents();
        ob_end_clean();
        do_action('mmc_admin_page', $content);
    }

    public static function showNav() {
        global $submenu, $plugin_page, $pagenow;
        $submenus = array();
        if (isset($submenu[self::MENU_OPTION])) {
            $thisMenu = $submenu[self::MENU_OPTION];
            foreach ($thisMenu as $item) {
                $slug = $item[2];
                $isCurrent = $slug == $plugin_page;
                $submenus[] = array(
                    'link' => get_admin_url('', $pagenow . '?page=' . $slug),
                    'title' => $item[3],
                    'current' => $isCurrent
                );
            }
            require(MMC_PATH . '/views/admin_nav.php');
        }
    }

}

/**
 * MultiMailChimp widget
 *
 * @author Sebastian Palus
 * @version 0.1.0
 * @copyright Copyright (c) 2012, REC
 * @package MultiMailChimp/Library
 */
class MultiMailChimpWidget extends WP_Widget {

    /**
     * Create widget
     */
    public function MultiMailChimpWidget() {
        $widget_ops = array('classname' => 'MultiMailChimpWidget', 'description' => 'Allows user to choose which lists he wants to be subscribed to');
        $this->WP_Widget('MultiMailChimpWidget', 'Multi-MailChimp Widget', $widget_ops);
    }

    /**
     * Widget options form
     * @param WP_Widget $instance 
     */
    public function form($instance) {
        $instance = wp_parse_args((array) $instance, array('title' => ''));
        $title = $instance['title'];
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
        <p><label for="mmc_shortcode">Shortcode: </label><textarea class="widefat" id="mmc_shortcode" readonly>[mmc-display-lists]</textarea>
            <?php
        }

        /**
         * Update widget options
         * @param WP_Widget $new_instance
         * @param WP_Widget $old_instance
         * @return WP_Widget 
         */
        public function update($new_instance, $old_instance) {
            $instance = $old_instance;
            $instance['title'] = $new_instance['title'];
            return $instance;
        }

        /**
         * Render widget
         * 
         * @param array $args
         * @param WP_Widget $instance 
         */
        public function widget($args, $instance) {
            extract($args, EXTR_SKIP);

            echo $before_widget;
            $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

            if (!empty($title))
                echo $before_title . $title . $after_title;;

            // WIDGET CODE GOES HERE
            MultiMailChimp::displayLists('', true);

            echo $after_widget;
        }

    }
    ?>
