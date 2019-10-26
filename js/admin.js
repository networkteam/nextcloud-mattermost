$(document).ready(function() {
	$('#mattermost_save_button').on('click', function(event) {
		event.preventDefault();
		console.log("Save..");
		OCP.AppConfig.setValue(
			'mattermost', 'hook_url',
			$("#mattermost_hook_url").val()
		);
		OCP.AppConfig.setValue(
			'mattermost', 'magic_channel_regex',
			$("#mattermost_magic_channel_regex").val()
		);
		OCP.AppConfig.setValue(
			'mattermost', 'filter_regex',
			$("#mattermost_filter_regex").val()
		);
	});
});