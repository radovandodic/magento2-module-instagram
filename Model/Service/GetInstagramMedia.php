<?php

declare(strict_types=1);

namespace DodaSoft\Instagram\Model\Service;

use Psr\Log\LoggerInterface;
use DodaSoft\Instagram\Exception\InstagramException;

class GetInstagramMedia
{
    const API_URL            = 'https://graph.instagram.com';
    const API_VERSION        = 'v22.0';
    const API_USERID_PATTERN = '%s/%s/me?fields=user_id,username&access_token=%s';
    const API_MEDIA_PATTERN  = '%s/%s/%s/media?limit=50&access_token=%s';
    const API_IMAGE_PATTERN  = '%s/%s/%s/?fields=id,media_type,media_url,owner,like_count,permalink,timestamp&access_token=%s';

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var array
     */
    protected array $config;

    /**
     * @param GetConfig       $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        GetConfig $config,
        LoggerInterface $logger
    ) {
        $this->config = $config->execute();
        $this->logger = $logger;
    }

    /**
     * @return array|InstagramException
     */
    public function execute(): array|InstagramException
    {
        $mediaImages = [];
        if (
            empty($this->config['enabled'])
            || empty($this->config['token'])
        ) {
            return $mediaImages;
        }

        try {
            if ($userId = $this->getUserId($this->config['token'])) {
                if ($mediaArray = $this->getAllMedia($userId, $this->config['token'])) {
                    foreach ($mediaArray as $mediaImageId) {
                        $imageData = $this->getMediaImageUrl($mediaImageId, $this->config['token']);
                        // Can be CAROUSEL_ALBUM, IMAGE, or VIDEO.
                        // Skip VIDEO
                        if ($imageData['media_type'] == 'VIDEO') {
                            continue;
                        }
                        $mediaImages[] = $imageData;
                    }
                }
            }
        } catch (InstagramException $e) {
            $this->logger->critical($e);

            return [];
        }

        return $mediaImages;
    }

    /**
     * @param string $url
     *
     * @return array
     * @throws InstagramException
     */
    protected function callApi(string $url): array
    {
        try {
            $instagramConnection = curl_init(); // initializing
            curl_setopt($instagramConnection, CURLOPT_URL, $url);
            curl_setopt($instagramConnection, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($instagramConnection, CURLOPT_TIMEOUT, 20);
            curl_setopt($instagramConnection, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($instagramConnection);
            curl_close($instagramConnection);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InstagramException('Error: callApi() - cURL error: ' . curl_error($instagramConnection));
        }

        return (!empty($result)) ? json_decode($result, true) : [];
    }

    /**
     * @param string $token
     *
     * @return string|null
     */
    protected function getUserId(string $token): ?string
    {
        $url = sprintf(
            self::API_USERID_PATTERN,
            self::API_URL,
            self::API_VERSION,
            $token
        );
        $result = $this->callApi($url);

        return $result['user_id'] ?? null;
    }

    /**
     * @param string $userId
     * @param string $token
     *
     * @return array
     */
    protected function getAllMedia(string $userId, string $token): array
    {
        $mediaArray = [];
        $url = sprintf(
            self::API_MEDIA_PATTERN,
            self::API_URL,
            self::API_VERSION,
            $userId,
            $token
        );

        $result = $this->callApi($url);

        if (!empty($result['data'])) {
            foreach ($result['data'] as $media) {
                $mediaArray[] = $media['id'];
            }
        }

        return $mediaArray;
    }

    /**
     * @param string $imageId
     * @param string $token
     *
     * @return string|null
     */
    protected function getMediaImageUrl(string $imageId, string $token): ?array
    {
        $url = sprintf(
            self::API_IMAGE_PATTERN,
            self::API_URL,
            self::API_VERSION,
            $imageId,
            $token
        );

        $result = $this->callApi($url);
        if (!empty($result['media_url'])) {
            return $result;
        }

        return null;
    }
}
