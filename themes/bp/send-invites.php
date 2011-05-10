<form id = 'send-invites' class = 'standard-form' action = '<?php the_permalink(); ?>send-invites/send/' method = 'post'>
<h2>Send Invites&nbsp;&nbsp;<input type = 'submit' class = 'ep-submit' id = 'ep-submit' value = 'Invite' /></h2>


<?php wp_nonce_field( 'send_invites', 'ep_invite_nonce' ); ?>

<?php
if( kb_has_results( Array(), 'EP_Inviteable' ) ) :
	while( kb_results() ) : kb_the_result(); ?>
		<div class = 'ep-user' id = 'ep-user-<?php kb_the_data( 'id' ); ?>' >
			<input type = "checkbox" id = 'c-user-<?php kb_the_data( 'id' ); ?>' class = 'ep-checkbox' name = 'ep_invite[]' value = '<?php kb_the_data( 'id' ); ?>' >
			<a href = '<?php kb_the_data( 'permalink' ); ?>' class = 'ep-member-link'>
				<span class = 'ep-nicename'><?php kb_the_data( 'nicename' ); ?></span>
				<?php kb_the_data( 'avatar', Array( 'class' => 'ep-avatar' ) ); ?>
			</a>
		</div>
	<?php endwhile;
else: ?>
	<p><?php _e( "There are no members to send invites to.", "eventpress" ); ?></p>
<?php endif;

?>

</form>
