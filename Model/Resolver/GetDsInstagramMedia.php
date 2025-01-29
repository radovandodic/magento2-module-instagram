<?php

declare(strict_types=1);

namespace DodaSoft\Instagram\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GetDsInstagramMedia implements ResolverInterface
{
    private $getDsInstagramMediaDataProvider;

    /**
     * @param DataProvider\GetDsInstagramMedia $getDsInstagramMediaRepository
     */
    public function __construct(
        DataProvider\GetDsInstagramMedia $getDsInstagramMediaDataProvider
    ) {
        $this->getDsInstagramMediaDataProvider = $getDsInstagramMediaDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $getDsInstagramMediaData = $this->getDsInstagramMediaDataProvider->getGetDsInstagramMedia();
        return $getDsInstagramMediaData;
    }
}
