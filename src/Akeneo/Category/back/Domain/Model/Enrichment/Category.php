<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Model\Enrichment;

use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use Akeneo\Category\Domain\ValueObject\ValueCollection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Category
{
    public function __construct(
        private ?CategoryId $id,
        private Code $code,
        private ?LabelCollection $labels = null,
        private ?CategoryId $parentId = null,
        private ?CategoryId $rootId = null,
        private ?\DateTimeImmutable $updated = null,
        private ?ValueCollection $attributes = null,
        private ?PermissionCollection $permissions = null,
    ) {
    }

    /**
     * @param array{
     *     id: int,
     *     code: string,
     *     translations: string|null,
     *     parent_id: int|null,
     *     root_id: int|null,
     *     updated: string|null,
     *     value_collection: string|null,
     *     permissions: string|null
     * } $result
     */
    public static function fromDatabase(array $result): self
    {
        return new self(
            id: new CategoryId((int) $result['id']),
            code: new Code($result['code']),
            labels: $result['translations'] ?
                LabelCollection::fromArray(
                    json_decode($result['translations'], true, 512, JSON_THROW_ON_ERROR),
                ) : null,
            parentId: $result['parent_id'] ? new CategoryId((int) $result['parent_id']) : null,
            rootId: $result['root_id'] ? new CategoryId((int) $result['root_id']) : null,
            updated: $result['updated'] ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $result['updated']) : null,
            attributes: $result['value_collection'] ?
                ValueCollection::fromDatabase(json_decode($result['value_collection'], true)) : null,
            permissions: isset($result['permissions']) && $result['permissions'] ?
                PermissionCollection::fromArray(json_decode($result['permissions'], true)) : null,
        );
    }

    public function getId(): ?CategoryId
    {
        return $this->id;
    }

    public function getCode(): Code
    {
        return $this->code;
    }

    public function getLabels(): ?LabelCollection
    {
        return $this->labels;
    }

    public function getParentId(): ?CategoryId
    {
        return $this->parentId;
    }

    public function getRootId(): ?CategoryId
    {
        return $this->rootId;
    }

    public function getUpdated(): ?\DateTimeImmutable
    {
        return $this->updated;
    }

    public function isRoot(): bool
    {
        // supposedly equivalent conditions, belt and braces
        return $this->parentId === null || $this->rootId === $this->id;
    }

    public function getAttributes(): ?ValueCollection
    {
        return $this->attributes;
    }

    /**
     * @return array<string> (example: [seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d])
     */
    public function getAttributeCodes(): array
    {
        if (null === $this->attributes) {
            return [];
        }

        $attributeCodes = [];
        foreach ($this->attributes as $attributeValues) {
            $attributeCodes[] = $attributeValues->getKey();
        }

        return array_values(array_unique($attributeCodes));
    }

    public function getPermissions(): ?PermissionCollection
    {
        return $this->permissions;
    }

    public function setLabel(string $localeCode, string $label): void
    {
        $this->labels->setTranslation($localeCode, $label);
    }

    public function setAttributes(ValueCollection $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array{
     *     id: int|null,
     *     parent: int|null,
     *     root_id: int | null,
     *     properties: array{
     *       code: string,
     *       labels: array<string, string>|null
     *     },
     *     attributes: array<string, array<string, mixed>> | null,
     *     permissions: array<string, array<int>>|null
     * }
     */
    public function normalize(): array
    {
        return [
            'id' => $this->getId()?->getValue(),
            'parent' => $this->getParentId()?->getValue(),
            'root_id' => $this->getRootId()?->getValue(),
            'properties' => [
                'code' => (string) $this->getCode(),
                'labels' => $this->getLabels()?->normalize(),
            ],
            'attributes' => $this->getAttributes()?->normalize(),
            'permissions' => $this->getPermissions()?->normalize(),
        ];
    }
}