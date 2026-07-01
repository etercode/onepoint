<?php

namespace App\Dto;

use App\Entity\CategoryAttribute;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Create/replace payload for a category attribute (admin). `code` is optional;
 * when omitted it is derived from the label. `options` only applies to
 * select-type attributes.
 */
class CategoryAttributeWriteRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 120)]
        public string $label = '',

        #[Assert\Length(max: 80)]
        public ?string $code = null,

        #[Assert\Choice(choices: CategoryAttribute::TYPES)]
        public string $type = CategoryAttribute::TYPE_TEXT,

        #[Assert\Length(max: 20)]
        public ?string $unit = null,

        /**
         * @var list<string>|null
         */
        #[Assert\All([new Assert\Type('string'), new Assert\Length(max: 120)])]
        public ?array $options = null,

        public bool $required = false,

        public bool $filterable = false,

        #[Assert\PositiveOrZero]
        public ?int $sortOrder = null,
    ) {
    }
}
