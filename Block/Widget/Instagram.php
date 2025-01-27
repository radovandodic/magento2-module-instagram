<?php

declare(strict_types=1);

namespace DodaSoft\Instagram\Block\Widget;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\CacheInterface;
use DodaSoft\Instagram\Model\Service\GetInstagramMedia;
use DodaSoft\Instagram\Model\Service\GetConfig;

class Instagram extends Template implements BlockInterface
{
    const CACHE_ID       = 'ds_instagram_widget_data';
    const CACHE_LIFETIME = 86400; // max 1 day, as instagram CDN will return "URL signature expired"
    const INSTAGRAM_URL  = 'https://www.instagram.com/';

    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * @var CacheInterface
     */
    protected CacheInterface $cache;

    /**
     * @var GetInstagramMedia
     */
    protected GetInstagramMedia $getInstagramMedia;

    /**
     * @var array
     */
    protected array $config;

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = "widget/instagram.phtml";

    /**
     * @param Context             $context
     * @param GetInstagramMedia   $getInstagramMedia
     * @param CacheInterface      $cache
     * @param SerializerInterface $serializer
     * @param GetConfig           $getConfig
     * @param array               $data
     */
    public function __construct(
        Context $context,
        GetInstagramMedia $getInstagramMedia,
        CacheInterface $cache,
        SerializerInterface $serializer,
        GetConfig $getConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->getInstagramMedia = $getInstagramMedia;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->config = $getConfig->execute();
    }

    /**
     * @return array
     */
    public function getInstagramMedia(): array
    {
        $data = $this->cache->load(self::CACHE_ID);
        if (false === $data) {
            $data = $this->getInstagramMedia->execute();
            $this->cache->save(
                $this->serializer->serialize($data),
                self::CACHE_ID,
                [],
                self::CACHE_LIFETIME
            );
        } else {
            $data = $this->serializer->unserialize($data);
        }

        return array_slice($data, 0, (int)$this->getData('noOfImages') ?? 10);
    }

    /**
     * @return bool
     */
    public function isInstagramWidgetEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    /**
     * @param string|null $permalink
     *
     * @return string
     */
    public function getInstagramUrl(?string $permalink): string
    {
        if (!empty($permalink)) {
            return $permalink;
        }

        return self::INSTAGRAM_URL . $this->config['username'] ?? '';
    }

    /**
     * @return string
     */
    public function getBlockUniqueId(): string
    {
        return rand() . time();
    }

    /**
     * @return string
     */
    public function getImageHeight(): string
    {
        if (!empty($this->config['imgHeight'])) {
            return 'height: ' . $this->config['imgHeight'] . 'px;';
        }

        return '';
    }
}
