<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\ProductModel;

use Akeneo\Test\Integration\Configuration;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use Symfony\Component\HttpFoundation\Response;

class CreateProductModelIntegration extends AbstractProductModelTestCase
{
    public function testSubProductModelCreation()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "sub_product_model",
        "family_variant": "familyVariantA1",
        "parent": "sweat",
        "values": {
          "a_simple_select": [
            {
              "locale": null,
              "scope": null,
              "data": "optionB"
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);

        $expectedProductModel = [
            'code'           => 'sub_product_model',
            'family_variant' => 'familyVariantA1',
            'parent'         => 'sweat',
            'categories'     => [],
            'values'        => [
                'a_price'                            => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            ['amount' => '50.00', 'currency' => 'EUR'],
                        ],
                    ],
                ],
                'a_localized_and_scopable_text_area' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => 'I like sweat!',
                    ],
                ],
                'a_simple_select' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'optionB',
                    ],
                ],
                'a_number_float' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '12.5000',
                    ],
                ],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProductModels($expectedProductModel, 'sub_product_model');
    }

    public function testSubProductModelCreationWithNoFamilyVariantProvided()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "sub_product_model",
        "parent": "sweat",
        "values": {
          "a_simple_select": [
            {
              "locale": null,
              "scope": null,
              "data": "optionB"
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);

        $expectedProductModel = [
            'code'           => 'sub_product_model',
            'family_variant' => 'familyVariantA1',
            'parent'         => 'sweat',
            'categories'     => [],
            'values'        => [
                'a_price'                            => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            ['amount' => '50.00', 'currency' => 'EUR'],
                        ],
                    ],
                ],
                'a_localized_and_scopable_text_area' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => 'I like sweat!',
                    ],
                ],
                'a_simple_select' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'optionB',
                    ],
                ],
                'a_number_float' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '12.5000',
                    ],
                ],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProductModels($expectedProductModel, 'sub_product_model');
    }

    public function testSubProductModelCreationWithAFamilyVariantDifferentFromTheParent()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "code": "sub_product_model",
        "parent": "sweat",
        "family_variant": "familyVariantA2",
        "values": {
          "a_simple_select": [
            {
              "locale": null,
              "scope": null,
              "data": "optionB"
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "family_variant" cannot be modified, "familyVariantA2" given. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_product_model'
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testSubProductModelCreationWithAFamilyVariantWithOnlyOneLevelOfVariant()
    {
        $this->createProductModel(
            [
                'code'           => 'tshirt_sub_product_model',
                'family_variant' => 'familyVariantA1',
                'parent'         => 'tshirt',
                'values'         => [
                    'a_simple_select' => [
                        [
                            'scope'  => null,
                            'locale' => null,
                            'data'   => "optionB",
                        ],
                    ],
                ],
            ]
        );

        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "code": "sub_product_model",
        "parent": "tshirt_sub_product_model",
        "family_variant": "familyVariantA1",
        "values": {
          "a_yes_no": [
            {
              "locale": null,
              "scope": null,
              "data": true
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => '',
                    'message'  => 'The product model "sub_product_model" cannot have the product model "tshirt_sub_product_model" as parent',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testSubProductModelCreationWithAParentThatIsNotARootProductModel()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "code": "sub_product_model",
        "parent": "sweat",
        "family_variant": "familyVariantA2",
        "values": {
          "a_simple_select": [
            {
              "locale": null,
              "scope": null,
              "data": "optionB"
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "family_variant" cannot be modified, "familyVariantA2" given. Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_product_model'
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testSubProductModelCreationWithNoValuesForTheAxeDefinedInParent()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "code": "sub_product_model",
        "parent": "sweat",
        "family_variant": "familyVariantA1",
        "values": {
          "a_simple_select": [
            {
              "locale": null,
              "scope": null,
              "data": null
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);


        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'attribute',
                    'message'  => 'Attribute "a_simple_select" cannot be empty, as it is defined as an axis for this entity',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testSubProductModelCreationWithAttributeNotInTheAttributeSet()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "code": "sub_product_model",
        "parent": "sweat",
        "family_variant": "familyVariantA1",
        "values": {
          "a_text_area": [
            {
              "locale": null,
              "scope": null,
              "data": "toto"
            }
          ],
          "a_simple_select": [
            {
              "locale": null,
              "scope": null,
              "data": "optionB"
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);


        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => '',
                    'message'  => 'Cannot set the property "a_text_area" to this entity as it is not in the attribute set',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testSubProductModelCreationWithAttributeNotInTheFamily()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "code": "sub_product_model",
        "parent": "sweat",
        "family_variant": "familyVariantA1",
        "values": {
          "a_metric_negative": [{
              "locale": null,
              "scope": null,
              "data": {
                  "amount": "-20.5000",
                  "unit": "CELSIUS"
              }
          }],
          "a_simple_select": [
            {
              "locale": null,
              "scope": null,
              "data": "optionB"
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);


        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => '',
                    'message'  => 'Attribute "a_metric_negative" does not belong to the family "familyA"',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testSubProductModelCreationWithoutCode()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "parent": "sweat",
        "family_variant": "familyVariantA1",
        "values": {
          "a_simple_select": [
            {
              "locale": null,
              "scope": null,
              "data": "optionB"
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);


        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'code',
                    'message'  => 'The product model code must not be empty.',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testSubProductModelCreationWithoutMissingScope()
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "code": "sub_product_model",
        "parent": "sweat",
        "family_variant": "familyVariantA1",
        "values": {
          "a_simple_select": [
            {
              "locale": null,
              "data": "optionB"
            }
          ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Property "a_simple_select" expects an array with the key "scope". Check the standard format documentation.',
            '_links'  => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_product_model'
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testRootProductModelCreation()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "root_product_model",
        "family_variant": "familyVariantA1",
        "values": {
            "a_number_float":[
                {
                    "locale":null,
                    "scope":null,
                    "data":"12.5000"
                }
            ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);

        $expectedProductModel = [
            'code'           => 'root_product_model',
            'family_variant' => 'familyVariantA1',
            'parent'         => null,
            'categories'     => [],
            'values'        => [
                'a_number_float' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '12.5000',
                    ],
                ],
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSameProductModels($expectedProductModel, 'root_product_model');
    }

    public function testRootProductModelCreationImportWithNoVariantFamily()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "root_product_model",
        "values": {
            "a_number_float":[
                {
                    "locale":null,
                    "scope":null,
                    "data":"12.5000"
                }
            ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => 'family_variant',
                    'message'  => 'The product model family variant must not be empty.',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testRootProductModelCreationImportWithAnAxeAsAttribute()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "root_product_model",
        "family_variant": "familyVariantA1",
        "values": {
            "a_simple_select":[
                {
                    "locale":null,
                    "scope":null,
                    "data":"optionB"
                }
            ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => '',
                    'message'  => 'Cannot set the property "a_simple_select" to this entity as it is not in the attribute set',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testRootProductModelCreationImportWithParentSetToNull()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
    {
        "code": "root_product_model",
        "family_variant": "familyVariantA1",
        "parent": null,
        "values": {
            "a_simple_select":[
                {
                    "locale":null,
                    "scope":null,
                    "data":"optionB"
                }
            ]
        }
    }
JSON;

        $client->request('POST', 'api/rest/v1/product-models', [], [], [], $data);

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => '',
                    'message'  => 'Cannot set the property "a_simple_select" to this entity as it is not in the attribute set',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    /**
     * @param array  $expectedProductModel normalized data of the product model that should be created
     * @param string $identifier           identifier of the product that should be created
     */
    protected function assertSameProductModels(array $expectedProductModel, $identifier)
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier($identifier);
        $standardizedProductModel = $this->get('pim_serializer')->normalize($productModel, 'standard');

        NormalizedProductCleaner::clean($expectedProductModel);
        NormalizedProductCleaner::clean($standardizedProductModel);

        $this->assertSame($expectedProductModel, $standardizedProductModel);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalCatalogPath()]);
    }
}
