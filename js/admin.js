$(document).ready(function() {
	$('#mattermost_save_button').on('click', function(event) {
		event.preventDefault();
		console.log("Save..");
		OCP.AppConfig.setValue(
			'mattermost', 'hook_url',
			$("#mattermost_hook_url").val()
		);
	});
});