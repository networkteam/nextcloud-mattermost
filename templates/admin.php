<?php
/** @var $l \OCP\IL10N */
/** @var $_ array */

script('mattermost', 'admin');
//style('mattermost', 'admin');    // adds a CSS file
?>

<div id="mattermost" class="section">
	<h2><?php p($l->t('Mattermost')); ?></h2>

	<form class="section" id="mattermost_form">
		<h3><?php p($l->t('Incoming Webhook url')); ?></h3>
		<p class="settings-hint">Visit Mattermost => Integrations => Create an incoming Webhook</p>
		<input type="text" name="mattermost_hook_url" id="mattermost_hook_url" style="width: 100%; max-width: 400px" placeholder="https://mattermost.your-domain.com/hooks/abcdef123"
			   value="<?php echo $_['hook_url'] ?>"/><br>

		<h3>Magic channel</h3>
		<p class="settings-hint">Use regex to post in channels matching group names. Leave empty to post in webhooks default channel. Notice: Deactivate "Lock to this channel" when using the regex (Default)</p>
		<input type="text" name="mattermost_magic_channel_regex" id="mattermost_magic_channel_regex" style="width: 100%; max-width: 400px"
			   value="<?php echo $_['magic_channel_regex'] ?>" placeholder="/^(project-.*)/"/><br><br>
		<button id="mattermost_save_button"><?php p($l->t('Save')); ?></button>
	</form>
</div>