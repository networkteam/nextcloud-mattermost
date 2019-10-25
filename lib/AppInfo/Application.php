<?php

namespace OCA\Mattermost\AppInfo;

use OCP\AppFramework\App;

use OCA\Mattermost\Hooks\FileHooks;
use OCP\Files\Node;
use Symfony\Component\EventDispatcher\GenericEvent;

class Application extends App {

	public function register() {
		$dispatcher = $this->getContainer()->getServer()->getEventDispatcher();
		$dispatcher->addListener('\OCP\Files::postCreate', function (GenericEvent $event) {
			if ($event->getSubject() instanceof Node) {
				try {
					FileHooks::postCreate($event->getSubject(), $this->getContainer()->getServer());
				} catch (\Exception $exception) {
				}
			}
		});
	}
}