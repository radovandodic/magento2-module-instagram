<?php

declare(strict_types=1);

namespace DodaSoft\Instagram\Model\Service;

use Psr\Log\LoggerInterface;
use DodaSoft\Instagram\Exception\InstagramException;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class DownloadInstagramMedia
{
    const INSTAGRAM_MEDIA_DIR = 'instagram_media/';

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var WriteInterface
     */
    protected WriteInterface $mediaDirectory;

    /**
     * @var AdapterFactory
     */
    protected AdapterFactory $adapterFactory;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var array
     */
    protected array $instagramMedia;

    /**
     * @param LoggerInterface       $logger
     * @param Filesystem            $filesystem
     * @param StoreManagerInterface $storeManager
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        LoggerInterface $logger,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->storeManager = $storeManager;
    }

    /**
     * @param array $instagramMedia
     * @param int   $width
     *
     * @return array|InstagramException
     * @throws InstagramException
     * @throws \ImagickException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(
        array $instagramMedia,
        int $width = 350
    ): array|InstagramException {
        $this->instagramMedia = $instagramMedia;
        try {
            $this->downloadFile($width);
        } catch (InstagramException $e) {
            $this->logger->critical($e);
            throw new InstagramException($e->getMessage());
        }

        return $this->instagramMedia;
    }

    /**
     * @param int $width
     *
     * @return void
     * @throws \ImagickException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function downloadFile(int $width): void
    {
        $directory = $this->getAbsoluteDirectoryPathToSaveMedia();
        $filename = $this->getNewFileName();
        if ($this->isImageAlreadySavedToMedia()) {
            return;
        }
        $filenameUrl = $this->getNewMediaUrl();
        $imageContent = file_get_contents($this->instagramMedia['media_url']);

        // use Imagick if posible and convert to webp format
        if (class_exists("Imagick")) {
            $filenameUrl .= '.webp';
            $im = new \Imagick();
            $im->readImageBlob($imageContent);
            $im->scaleImage($width, 0, false);
            $im->setImageFormat('webp');
            $im->setOption('webp:method', '6');
            $im->writeImage($dirForSave . $filename . '.webp');
            $im->clear();
            $im->destroy();
        } else {
            $filenameUrl .= '.jpg';
            file_put_contents($dirForSave . $filename . '.jpg', $imageContent);
        }

        $this->instagramMedia['media_url'] = $filenameUrl;

    }

    /**
     * @return bool
     */
    protected function isImageAlreadySavedToMedia(): bool
    {
        $filePath = $this->getAbsoluteDirectoryPathToSaveMedia() . $this->getNewFileName();
        if (
            file_exists($filePath. '.webp')
        ) {
            $this->instagramMedia['media_url'] = $this->getNewMediaUrl() . '.webp';

            return true;
        } elseif (
            file_exists($filePath . '.jpeg')
        ) {
            $this->instagramMedia['media_url'] = $this->getNewMediaUrl() . '.jpeg';

            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    protected function getAbsoluteDirectoryPathToSaveMedia(): string
    {
        $dirForSave = $this->mediaDirectory->getAbsolutePath(self::INSTAGRAM_MEDIA_DIR);
        if (!is_dir($dirForSave)) {
            mkdir($dirForSave, 0777, true);
        }

        return $dirForSave;
    }

    /**
     * @return string
     */
    protected function getNewFileName(): string
    {
        return $this->instagramMedia['id'];
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getNewMediaUrl(): string
    {
        return sprintf(
            '%s%s%s',
            $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA),
            self::INSTAGRAM_MEDIA_DIR,
            $this->getNewFileName()
        );
    }
}
