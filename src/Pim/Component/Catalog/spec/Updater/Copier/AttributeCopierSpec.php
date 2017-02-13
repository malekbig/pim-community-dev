<?php

namespace spec\Pim\Component\Catalog\Updater\Copier;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeCopierSpec extends ObjectBehavior
{
    function let(
        ProductBuilderInterface $builder,
        AttributeValidatorHelper $attrValidatorHelper,
        NormalizerInterface $normalizer
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            $normalizer,
            ['foo', 'bar'],
            ['foo', 'bar']
        );
    }

    function it_is_a_copier()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Copier\CopierInterface');
    }

    function it_supports_attributes(
        AttributeInterface $fromFooAttribute,
        AttributeInterface $toFooAttribute,
        AttributeInterface $fromTextareaAttribute,
        AttributeInterface $fromImageAttribute,
        AttributeInterface $toImageAttribute,
        AttributeInterface $fromFileAttribute,
        AttributeInterface $toFileAttribute,
        AttributeInterface $toTextareaAttribute
    ) {
        $fromFooAttribute->getAttributeType()->willReturn('foo');
        $toFooAttribute->getAttributeType()->willReturn('foo');
        $this->supportsAttributes($fromFooAttribute, $toFooAttribute)->shouldReturn(true);

        $fromFooAttribute->getAttributeType()->willReturn('foo');
        $toFooAttribute->getAttributeType()->willReturn('bar');
        $this->supportsAttributes($fromFooAttribute, $toFooAttribute)->shouldReturn(false);

        $fromTextareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $toTextareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supportsAttributes($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(false);

        $fromImageAttribute->getAttributeType()->willReturn('pim_catalog_image');
        $toImageAttribute->getAttributeType()->willReturn('pim_catalog_image');
        $this->supportsAttributes($fromImageAttribute, $toImageAttribute)->shouldReturn(false);

        $fromFileAttribute->getAttributeType()->willReturn('pim_catalog_file');
        $toFileAttribute->getAttributeType()->willReturn('pim_catalog_file');
        $this->supportsAttributes($fromImageAttribute, $toImageAttribute)->shouldReturn(false);

        $fromFooAttribute->getAttributeType()->willReturn('foo');
        $toTextareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supportsAttributes($fromFooAttribute, $toTextareaAttribute)->shouldReturn(false);
    }

    function it_copies_a_boolean_value_to_a_product_value(
        $builder,
        $attrValidatorHelper,
        $normalizer,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductValue $fromProductValue,
        ProductValue $toProductValue
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $normalizer->normalize($fromProductValue, 'standard')->willReturn(true);

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $builder
            ->addOrReplaceProductValue($product1, $toAttribute, $toLocale, $toScope, true)
            ->willReturn($toProductValue);

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $builder
            ->addOrReplaceProductValue($product2, $toAttribute, $toLocale, $toScope, null)
            ->shouldNotBeCalled();

        $products = [$product1, $product2];
        foreach ($products as $product) {
            $this->copyAttributeData(
                $product,
                $product,
                $fromAttribute,
                $toAttribute,
                [
                    'from_locale' => $fromLocale,
                    'to_locale' => $toLocale,
                    'from_scope' => $fromScope,
                    'to_scope' => $toScope
                ]
            );
        }
    }

    function it_copies_a_date_value_to_a_product_value(
        $builder,
        $attrValidatorHelper,
        $normalizer,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductValue $fromProductValue,
        ProductValue $toProductValue
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $normalizer->normalize($fromProductValue, 'standard')->willReturn('1970-01-01');

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $builder
            ->addOrReplaceProductValue($product1, $toAttribute, $toLocale, $toScope, '1970-01-01')
            ->willReturn($toProductValue);

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $builder
            ->addOrReplaceProductValue($product2, $toAttribute, $toLocale, $toScope, null)
            ->shouldNotBeCalled();

        $products = [$product1, $product2];
        foreach ($products as $product) {
            $this->copyAttributeData(
                $product,
                $product,
                $fromAttribute,
                $toAttribute,
                [
                    'from_locale' => $fromLocale,
                    'to_locale' => $toLocale,
                    'from_scope' => $fromScope,
                    'to_scope' => $toScope
                ]
            );
        }
    }

    function it_copies_number_value_to_a_product_value(
        $builder,
        $attrValidatorHelper,
        $normalizer,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductValue $fromProductValue,
        ProductValue $toProductValue
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $normalizer->normalize($fromProductValue, 'standard')->willReturn(123);

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $builder
            ->addOrReplaceProductValue($product1, $toAttribute, $toLocale, $toScope, 123)
            ->willReturn($toProductValue);

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $builder
            ->addOrReplaceProductValue($product2, $toAttribute, $toLocale, $toScope, null)
            ->shouldNotBeCalled();

        $products = [$product1, $product2];
        foreach ($products as $product) {
            $this->copyAttributeData(
                $product,
                $product,
                $fromAttribute,
                $toAttribute,
                [
                    'from_locale' => $fromLocale,
                    'to_locale' => $toLocale,
                    'from_scope' => $fromScope,
                    'to_scope' => $toScope
                ]
            );
        }
    }

    function it_copies_text_value_to_a_product_value(
        $attrValidatorHelper,
        $builder,
        $normalizer,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductValue $fromProductValue,
        ProductValue $toProductValue
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $normalizer->normalize($fromProductValue, 'standard')->willReturn('data');

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $builder
            ->addOrReplaceProductValue($product1, $toAttribute, $toLocale, $toScope, 'data')
            ->willReturn($toProductValue);

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $builder
            ->addOrReplaceProductValue($product2, $toAttribute, $toLocale, $toScope, null)
            ->shouldNotBeCalled();

        $products = [$product1, $product2];
        foreach ($products as $product) {
            $this->copyAttributeData(
                $product,
                $product,
                $fromAttribute,
                $toAttribute,
                [
                    'from_locale' => $fromLocale,
                    'to_locale' => $toLocale,
                    'from_scope' => $fromScope,
                    'to_scope' => $toScope
                ]
            );
        }
    }

    function it_throws_an_exception_when_locale_is_expected(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" expects a locale, none given.');
        $fromAttribute->getCode()->willReturn('attributeCode');
        $fromAttribute->isLocalizable()->willReturn(true);
        $attrValidatorHelper->validateLocale($fromAttribute, null)->willThrow($e);
        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'attributeCode',
                'Pim\Component\Catalog\Updater\Copier\AttributeCopier',
                $e
            )
        )->during('copyAttributeData', [$product, $product, $fromAttribute, $toAttribute, []]);
    }

    function it_throws_an_exception_when_locale_is_not_expected(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" does not expect a locale, "en_US" given.');
        $fromAttribute->getCode()->willReturn('attributeCode');
        $fromAttribute->isLocalizable()->willReturn(false);
        $attrValidatorHelper->validateLocale($fromAttribute, 'en_US')->willThrow($e);
        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'attributeCode',
                'Pim\Component\Catalog\Updater\Copier\AttributeCopier',
                $e
            )
        )->during(
            'copyAttributeData',
            [$product, $product, $fromAttribute, $toAttribute, ['from_locale' => 'en_US', 'from_scope' => 'ecommerce']]
        );
    }

    function it_throws_an_exception_when_locale_is_expected_but_not_activated(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" expects an existing and activated locale, "uz-UZ" given.');
        $fromAttribute->getCode()->willReturn('attributeCode');
        $fromAttribute->isLocalizable()->willReturn(true);
        $attrValidatorHelper->validateLocale($fromAttribute, 'uz-UZ')->willThrow($e);
        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'attributeCode',
                'Pim\Component\Catalog\Updater\Copier\AttributeCopier',
                $e
            )
        )->during(
            'copyAttributeData',
            [$product, $product, $fromAttribute, $toAttribute, ['from_locale' => 'uz-UZ', 'from_scope' => 'ecommerce']]
        );
    }

    function it_throws_an_exception_when_scope_is_expected(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" expects a scope, none given.');
        $fromAttribute->getCode()->willReturn('attributeCode');
        $fromAttribute->isLocalizable()->willReturn(false);
        $fromAttribute->isScopable()->willReturn(true);
        $attrValidatorHelper->validateLocale($fromAttribute, null)->shouldBeCalled();
        $attrValidatorHelper->validateScope($fromAttribute, null)->willThrow($e);
        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'attributeCode',
                'Pim\Component\Catalog\Updater\Copier\AttributeCopier',
                $e
            )
        )->during(
            'copyAttributeData',
            [$product, $product, $fromAttribute, $toAttribute, ['from_locale' => null, 'from_scope' => null]]
        );
    }

    function it_throws_an_exception_when_scope_is_not_expected(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" does not expect a scope, "ecommerce" given.');
        $fromAttribute->getCode()->willReturn('attributeCode');
        $fromAttribute->isLocalizable()->willReturn(false);
        $fromAttribute->isScopable()->willReturn(false);
        $attrValidatorHelper->validateLocale($fromAttribute, null)->shouldBeCalled();
        $attrValidatorHelper->validateScope($fromAttribute, 'ecommerce')->willThrow($e);
        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'attributeCode',
                'Pim\Component\Catalog\Updater\Copier\AttributeCopier',
                $e
            )
        )->during(
            'copyAttributeData',
            [$product, $product, $fromAttribute, $toAttribute, ['from_locale' => null, 'from_scope' => 'ecommerce']]
        );
    }

    function it_throws_an_exception_when_scope_is_expected_but_not_existing(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" expects an existing scope, "ecommerce" given.');
        $fromAttribute->getCode()->willReturn('attributeCode');
        $fromAttribute->isLocalizable()->willReturn(false);
        $fromAttribute->isScopable()->willReturn(true);
        $attrValidatorHelper->validateLocale($fromAttribute, null)->shouldBeCalled();
        $attrValidatorHelper->validateScope($fromAttribute, 'ecommerce')->willThrow($e);
        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'attributeCode',
                'Pim\Component\Catalog\Updater\Copier\AttributeCopier',
                $e
            )
        )->during(
            'copyAttributeData',
            [$product, $product, $fromAttribute, $toAttribute, ['from_locale' => null, 'from_scope' => 'ecommerce']]
        );
    }
}
