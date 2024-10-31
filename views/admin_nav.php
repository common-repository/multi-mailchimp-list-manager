<?php
/**
 * CM Multi MailChimp navigation bar
 *
 * @author CreativeMinds (http://plugins.cminds.com)
 * @version 1.2
 * @copyright Copyright (c) 2012, CreativeMinds
 * @package MultiMailChimp/View
 */
?>
<style type="text/css">
    .subsubsub li+li:before {content:'| ';}
</style>
<ul class="subsubsub">
    <?php foreach ($submenus as $menu): ?>
    <li><a href="<?php echo $menu['link']; ?>" <?php echo ($menu['current'])?'class="current"':''; ?>><?php echo $menu['title']; ?></a></li>
    <?php endforeach; ?>
</ul>
