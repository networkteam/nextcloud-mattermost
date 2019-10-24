<?php
/** @var $l \OCP\IL10N */
/** @var $_ array */

script('mattermost', 'admin');         // adds a JavaScript file
//style('mattermost', 'admin');    // adds a CSS file
?>

<div id="mattermost" class="section">
	<h2><?php p($l->t('Mattermost')); ?></h2>

	<form class="section" id="mattermost_form">
		<label><?php p($l->t('Incoming Webhook url')); ?></label>
		<input type="text" name="mattermost_hook_url" id="mattermost_hook_url" style="width: 100%; max-width: 500px"
			   value="<?php echo $_['hook_url'] ?>"/>
		<button id="mattermost_save_button"><?php p($l->t('Save')); ?></button>
	</form>
</div>