<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Validation;

use Akeneo\Catalogs\Infrastructure\Validation\ProductMappingSchema;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductMappingSchema
 * @covers \Akeneo\Catalogs\Infrastructure\Validation\ProductMappingSchemaValidator
 */
class ProductMappingSchemaValidatorTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @dataProvider validSchemaDataProvider
     */
    public function testItAcceptsTheSchema(string $raw): void
    {
        $schema = \json_decode($raw, false, 512, JSON_THROW_ON_ERROR);

        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($schema, new ProductMappingSchema());

        $this->assertEmpty($violations);
    }

    /**
     * @dataProvider invalidSchemaDataProvider
     */
    public function testItRejectsTheSchema(string $raw): void
    {
        $schema = \json_decode($raw, false, 512, JSON_THROW_ON_ERROR);
        if (!\property_exists($schema, 'description')) {
            throw new \LogicException('An invalid schema should have a "description" with the expected error.');
        }

        /** @var array<ConstraintViolation> $violations */
        $violations = self::getContainer()->get(ValidatorInterface::class)->validate($schema, new ProductMappingSchema());

        $this->assertCount(1, $violations);
        $this->assertEquals('You must provide a valid schema.', $violations[0]->getMessage());
        $this->assertEquals(
            $schema->description,
            $violations[0]->getCause(),
            'The invalid schema contains a "description" with the error that was expected.',
        );
    }

    public function validSchemaDataProvider(): array
    {
        return $this->readFilesFromDirectory(__DIR__ . '/ProductSchema/valid');
    }

    public function invalidSchemaDataProvider(): array
    {
        return $this->readFilesFromDirectory(__DIR__ . '/ProductSchema/invalid');
    }

    /**
     * @return array<string, array{raw: string}>
     */
    private function readFilesFromDirectory(string $directory): array
    {
        $files = \scandir($directory);
        $files = \array_filter($files, fn ($file): bool => !\str_starts_with($file, '.'));

        $allFiles = \array_reduce($files, function (array $carry, string $file) use ($directory): array {
            $path = $directory . '/' . $file;

            if (\is_dir($directory . '/' . $file)) {
                $carry[] = $this->readFilesFromDirectory($path);
            } else {
                $carry[] = [
                    $file => [
                        'raw' => \file_get_contents($directory . '/' . $file),
                    ],
                ];
            }

            return $carry;
        }, []);

        return \array_merge(...$allFiles);
    }
}
