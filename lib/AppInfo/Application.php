<?php

namespace OCA\Mattermost\AppInfo;

use OCP\AppFramework\App;
use OCA\Mattermost\Hooks\FileHooks;
use OCP\Files\Node;
use Symfony\Component\EventDispatcher\GenericEvent;

class Application extends App {

	public function register() {
		// Nextcloud 17
		$rootFolder = $this->getContainer()->getServer()->getRootFolder();
		$server = $this->getContainer()->getServer();
		$rootFolder->listen('\OC\Files', 'postCreate', function (Node $file) use ($server) {
			FileHooks::postCreate($file, $server);
		});

		// Nextcloud 18
		/*
		$dispatcher = $this->getContainer()->getServer()->getEventDispatcher();
		$dispatcher->addListener('\OCP\Files::postCreate', function (GenericEvent $event) {
			if ($event->getSubject() instanceof Node) {
				try {
					FileHooks::postCreate($event->getSubject(), $this->getContainer()->getServer());
				} catch (\Exception $exception) {
				}
			}
		});
		*/
	}
}