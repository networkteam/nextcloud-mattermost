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
use Psr\Http\Message\ResponseInterface;

class FileHooks
{

    public static function postCreate(Node $file, Server $server): void
    {
        try {
            if (strpos($file->getPath(), '/preview/') !== false) {
                // Do not post when preview images are created
                throw new \Exception('Preview image is not supported.', 1572013690);
            }

            $hookUrl = self::getAppSetting('hook_url');

            if (empty($hookUrl)) {
                throw new \Exception('Setting "hook_url" is empty.', 1572013591);
            }

            $magicChannelRegex = self::getAppSetting('magic_channel_regex');
            $defaultPayload = self::getMattermostPayload($file, $server);

            if (empty($magicChannelRegex)) {
                self::makeMattermostRequest($hookUrl, $defaultPayload);
            } else {
                $channels = self::getChannels($magicChannelRegex, $file, $server);
                if (count($channels) > 0) {
                    foreach ($channels as $channel) {
                        $payload = array_merge($defaultPayload, [
                            'channel' => $channel
                        ]);

                        self::makeMattermostRequest($hookUrl, $payload);
                    }
                } else {
                    self::makeMattermostRequest($hookUrl, $defaultPayload);
                }
            }

            // TODO: Add background job if post failed

        } catch (\Exception $e) {

        }
    }

    /**
     * @param string $key
     * @return string
     * @throws \OCP\AppFramework\QueryException
     */
    protected static function getAppSetting(string $key): string
    {
        /** @var IConfig $settingsManager */
        $settingsManager = \OC::$server->query(IConfig::class);
        return $settingsManager->getAppValue('mattermost', $key);
    }

    /**
     * @param Node $file
     * @param Server $server
     * @return array
     * @throws \OCP\Files\InvalidPathException
     * @throws \OCP\Files\NotFoundException
     */
    protected static function getMattermostPayload(Node $file, Server $server): array
    {
        $url = $server->getURLGenerator()->linkToRouteAbsolute(
            'files.viewcontroller.showFile',
            [
                'fileid' => $file->getId()
            ]
        );

        $payload = [
            'username' => 'nextcloud',
            'text' => sprintf(
                "%s created file [%s](%s) in path *%s*.",
                $file->getFileInfo()->getOwner()->getDisplayName(),
                $file->getName(),
                $url,
                $file->getFileInfo()->getPath()
            )
        ];

        return $payload;
    }

    protected static function getChannels($magicChannelRegex, Node $file, $server): array
    {
        /** @var IShareProvider $shareProvider */
        $factory = new ProviderFactory($server);
        $shareProvider = $factory->getProviderForType(\OCP\Share::SHARE_TYPE_GROUP);
        $shares = $shareProvider->getSharesByPath($file->getParent());

        $groupMatches = [];
        /** @var Share $share */
        foreach ($shares as $share) {
            $isSharedGroupFolder = $share->getShareType() === \OCP\Share::SHARE_TYPE_GROUP && $share->getNodeType() === 'folder';

            if ($isSharedGroupFolder) {
                $matches = [];
                $groupName = $share->getSharedWith();

                preg_match(strtolower($magicChannelRegex), $groupName, $matches);

                if (count($matches) > 0) {
                    $groupMatches = array_merge($groupMatches, $matches);
                }
            }
        }

        return array_unique($groupMatches);
    }

    protected static function makeMattermostRequest(string $hookUrl, array $payload): ResponseInterface
    {
        $client = new Client();

        $response = $client->request('POST', $hookUrl, [
            'json' => $payload
        ]);

        // TODO: validate response

        return $response;
    }
}