<?php

namespace OCA\Mattermost\Hooks;

use GuzzleHttp\Client;
use OCP\Files\Node;
use OCP\IConfig;

class FileHooks {

	public static function postCreate(Node $file) {

		/** @var IConfig $settingsManager */
		$settingsManager = \OC::$server->query(IConfig::class);
		$hookUrl = $settingsManager->getAppValue('mattermost', 'hook_url');

		if ($hookUrl) {
			$client = new Client();
			$response = $client->request('POST', $hookUrl, [
				'json' => [
					'username' => 'nextcloud',
					'text' => sprintf("User %s created file %s in path %s", $file->getFileInfo()->getOwner()->getDisplayName(), $file->getFileInfo()->getName(), $file->getFileInfo()->getPath())
				]
			]);
		}
	}

}