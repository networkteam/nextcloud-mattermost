<?php

namespace OCA\Mattermost\Hooks;

use GuzzleHttp\Client;
use OC\Server;
use OC\Share20\ProviderFactory;
use OC\Share20\Share;
use OCP\Files\Node;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IUserSession;
use OCP\Share\IShareProvider;

class FileHooks {

	public static function postCreate(Node $file, Server $server) {
		if (strpos($file->getPath(), '/preview/') !== false) {
			// Do not post when preview images are created
			return;
		}

		/** @var IConfig $settingsManager */
		$settingsManager = \OC::$server->query(IConfig::class);

		$hookUrl = $settingsManager->getAppValue('mattermost', 'hook_url');
		if ($hookUrl) {
			$url = $server->getURLGenerator()->linkToRouteAbsolute('files.viewcontroller.showFile', ['fileid' => $file->getId()]);

			$markDownLink = sprintf("[%s](%s)", $file->getName(), $url);
			$payload = [
				'username' => 'nextcloud',
				'text' => sprintf("User %s created file %s in path %s", $file->getFileInfo()->getOwner()->getDisplayName(), $markDownLink, $file->getFileInfo()->getPath())
			];

			$client = new Client();
			$groupNameRegex = $settingsManager->getAppValue('mattermost', 'group_name_regex');
			if ($groupNameRegex) {
				$channels = self::getChannels($groupNameRegex, $file, $server);
				if (count($channels) > 0) {
					foreach ($channels as $channel) {
						$channelPayload = $payload;
						$channelPayload['channel'] = $channel;
						$response = $client->request('POST', $hookUrl, [
							'json' => $channelPayload
						]);
					}
				} else {
					$response = $client->request('POST', $hookUrl, [
						'json' => $payload
					]);
				}
			} else {
				$response = $client->request('POST', $hookUrl, [
					'json' => $payload
				]);
			}

			// Todo: Check response
			// Todo: Add background job if post failed
		}
	}

	protected static function getChannels($groupNameRegex, Node $file, $server): array {
		/** @var IUserSession $sessionManager */
		$sessionManager = \OC::$server->query(IUserSession::class);
		$currentUser = $sessionManager->getUser();

		/** @var IGroupManager $groupManager */
		$groupManager = \OC::$server->query(IGroupManager::class);
		$groups = $groupManager->getUserGroups($currentUser);

		/** @var IShareProvider $shareProvider */
		$factory = new ProviderFactory($server);
		$shareProvider = $factory->getProviderForType(\OCP\Share::SHARE_TYPE_GROUP);
		$shares = $shareProvider->getSharesByPath($file->getParent());

		$groups = [];
		/** @var Share $share */
		foreach ($shares as $share) {
			if ($share->getShareType() === \OCP\Share::SHARE_TYPE_GROUP && $share->getNodeType() === 'folder') {
				$groups[] = $share->getSharedWith();
			}
		}

		$groupsMatches = [];
		foreach ($groups as $groupName) {
			preg_match(strtolower($groupNameRegex), $groupName, $matches);
			if (count($matches) > 0) {
				$groupsMatches = array_merge($groupsMatches, $matches);
			}
		}

		return array_unique($groupsMatches);
	}
}