<?php if ( ep_have_registrants() ) : ?>

	<ul id="member-list" class="item-list">
		<?php while ( ep_have_registrants() ) : ep_the_registrant(); ?>
			<li class="member">
					<div class="item-avatar"><a href="<?php ep_registrant_domain() ?>">
					<?php ep_registrant_avatar_thumb() ?>
				</a>
			</div>
			<div class="item">
				<div class="item-title">
				<h5><a href = '<?php ep_registrant_domain(); ?>'><?php ep_the_author(); ?></a></h5>
				</div>
				<div class="item-meta"><span class="activity"><?php ep_registered_since() ?></span></div>
			</div>
			</li>
		<?php endwhile; ?>
	</ul>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'No one has registered yet.', 'eventpress' ); ?></p>
	</div>

<?php endif; ?>
