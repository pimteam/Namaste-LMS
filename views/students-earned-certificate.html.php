<div class="wrap">
	<h1><?php printf(__("Students Who Earned Certificate %s", 'namaste'), stripslashes($certificate->title))?></h1>
	
	<p><a href="admin.php?page=namaste_certificates"><?php _e('Back to all certificates', 'namaste')?></a></p>
	
	<?php if(!sizeof($users)):?>
		<p><?php _e('No student has earned this certificate yet.', 'namaste')?></p>
		</div>
	<?php return true;
	endif;?>
	
	<table class="widefat">
		<tr><th><?php _e('User name and email', 'namaste')?></th><th><?php _e('Date earned', 'namaste')?></th>
		<th><?php _e('View Certificate', 'namaste')?></th>		
		<th><?php _e('Remove', 'namaste')?></th></tr>	
		
		<?php foreach($users as $user):
			$class = ('alternate' == @$class) ? '' : 'alternate';?>
			<tr class="<?php echo $class?>"><td><?php echo $user->user_nicename . " (".$user->user_email . ")"?></td>
			<td><?php echo date( $dateformat, strtotime($user->date) ); ?></td>			
			<td><a href="<?php echo site_url('?namaste_view_certificate=1&id='.$certificate->id.'&student_id=' . $user->student_id . '&my_id=' . $user->student_certificate_id . '&noheader=1')?>" target="_blank"><?php _e('View / print', 'namaste')?></a></td>
			<td><a href="#" onclick="NamasteLMSRemoveUserCertificate(<?php echo $user->student_certificate_id?>);return false;"><?php _e('Remove', 'namaste')?></a></td></tr>
		<?php endforeach;?>
	</table>
</div>

<script type="text/javascript" >
function NamasteLMSRemoveUserCertificate(ucID) {
	if(confirm("<?php _e('Are you sure? The user will not be able to print this certificate.', 'namaste')?>")) {
		window.location = 'admin.php?page=namaste_student_certificates&id=<?php echo $certificate->id?>&delete=1&student_certificate_id=' + ucID;
	}
}
</script>