<?php

declare(strict_types=1);

namespace DodaSoft\Instagram\Model\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;

class GetConfig
{
    public const XML_INSTAGRAM_WIDGET_ENABLED         = 'dodasoft_instagram/configuration/enabled';
    public const XML_INSTAGRAM_WIDGET_API_TOKEN       = 'dodasoft_instagram/configuration/token';
    public const XML_INSTAGRAM_WIDGET_USERNAME        = 'dodasoft_instagram/configuration/username';
    public const XML_INSTAGRAM_WIDGET_CAROUSEL_HEIGHT = 'dodasoft_instagram/configuration/carousel_height';
    public const XML_INSTAGRAM_WIDGET_DOWNLOAD        = 'dodasoft_instagram/configuration/download';
    public const XML_INSTAGRAM_WIDGET_IMG_WIDTH       = 'dodasoft_instagram/configuration/img_width';

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        return [
            'enabled'         => $this->scopeConfig->isSetFlag(self::XML_INSTAGRAM_WIDGET_ENABLED),
            'token'           => $this->scopeConfig->getValue(self::XML_INSTAGRAM_WIDGET_API_TOKEN),
            'username'        => $this->scopeConfig->getValue(self::XML_INSTAGRAM_WIDGET_USERNAME),
            'carousel_height' => $this->scopeConfig->getValue(self::XML_INSTAGRAM_WIDGET_CAROUSEL_HEIGHT),
            'download'        => $this->scopeConfig->isSetFlag(self::XML_INSTAGRAM_WIDGET_DOWNLOAD),
            'img_width'       => $this->scopeConfig->getValue(self::XML_INSTAGRAM_WIDGET_IMG_WIDTH)
        ];
    }
}
