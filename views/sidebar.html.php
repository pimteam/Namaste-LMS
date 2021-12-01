<?php 
$current_plugins = get_option('active_plugins');
?>
<style type="text/css">
.namaste-sidebox
{
	border:1px solid black;
	padding:10px;
	margin-bottom: 20px;
}

#namaste-sidebar
{
	float:left;
	width:25%;
}

#namaste-related, #namaste-ad
{
	background-color: #BBEEDD;
}
</style>

<?php 
if(!in_array('namaste-pro/namaste-pro.php', $current_plugins)):?>
	<div id="namaste-related" class="namaste-sidebox">
		<h3><?php _e('Go PRO!', 'http://namaste-lms.org/pro.php')?></h3>
		
		<p><?php _e('Get more powerful features by', 'http://namaste-lms.org/pro.php')?> <a href="http://namaste-lms.org/pro.php" target="_blank"><?php _e('upgrading to the PRO version:', 'http://namaste-lms.org/pro.php')?></a></p>
		
		<ol>
			<li><strong><?php _e('Premium support', 'http://namaste-lms.org/pro.php')?></strong>. <?php _e('Free email support for 1 year after purchase', 'namaste')?></li>
			<li><strong><?php _e('Create classes and limit access to courses based on them.', 'namaste')?></strong>. </li>
			<li><strong><?php _e('Assign teachers to the classes to have your team members manage content.', 'namaste')?></strong></li>
			<li><strong><?php _e('Delayed access to course material.', 'namaste')?></strong> </li>
			<li><strong><?php _e('Shortcodes to let users sign-up for classes.', 'namaste')?></strong> </li>
			<li><strong><?php _e('Award badges for completing courses and earning points.', 'namaste')?></strong> </li>
			<li><strong><?php _e('Create discount coupons for paid courses and classes.', 'namaste');?></strong></li>
			<li><strong><?php _e('Boost your revenue by selling bundles of courses.', 'namaste');?></strong></li>
			<li><strong><?php _e('Connects to WooCommerce and WP Simple Shopping Cart.', 'namaste');?></strong></li>
			<li><strong><?php _e('Progress bar shows users how they do in each course.', 'namaste')?></strong> </li>
			<li><strong><?php _e('Localization-ready', 'namaste')?></strong> <?php _e('with translation .pot file', 'namaste')?></li>
			<li><strong><?php _e('Unlimited domains license', 'namaste')?></strong> <?php _e('for domains you own', 'namaste')?></li>		
			<li><?php _e('And more - just come to see!', 'namaste')?></li>
		</ol>
		
		<p><?php _e('Just go ahead and', 'namaste')?> <a href="ttp://namaste-lms.org/pro.php" target="_blank"><?php _e('take the tour', 'namaste')?></a> <?php _e('or check the', 'namaste')?> <a href="http://namaste-lms.org/pro.php#demo" target="_blank"><?php _e('online demo.', 'namaste')?></a></p>
		
		<p><?php _e('Upgrading to PRO comes with 60 daysrefund policy, one year free upgrades and one year free support.', 'namaste')?></p>
		
		<p><?php printf(__('See also the <a href="%s" target="_blank">Namaste! LMS Theme</a> for a quick start in designing your LMS site!', 'namaste'), 'http://namaste-lms.org/theme.php');?></p>
	</div>
<?php endif;?>

<div id="namaste-ad" class="namaste-sidebox">
	<h3><?php _e('Help To Spread The Word!', 'namaste')?></h3>
	
	<p><?php _e('If you like this plugin you can do some of the following:', 'namaste')?></p>
	
	<ol>
		<li><a href="http://wordpress.org/extend/plugins/namaste-lms/" target="_blank"><?php _e('Rate it 5 starts at Wordpress.org', 'namaste')?></a></li>
		<li><?php _e('Let others know about it by blogging or posting', 'namaste')?> <a href="http://namaste-lms.org/" target="_blank"><?php _e('our home page', 'namaste')?></a> <?php _e('on social networks.', 'namaste')?></li>
		<li><a href="http://namaste-lms.org/pro.php" target="_blank"><?php _e('Go PRO', 'namaste')?></a> <?php _e('to empower your site with extra benefits', 'namaste')?></li>
	</ol>
</div>

<div id="namaste-help" class="namaste-sidebox">
	<h3><?php _e('Need Support?', 'namaste')?></h3>
	
	<p><?php _e('Please ask your question in the', 'namaste')?> <a href="http://wordpress.org/support/plugin/namaste-lms" target="_blank"><?php _e('support forum at WordPress.', 'namaste')?></a></p>	
</div>