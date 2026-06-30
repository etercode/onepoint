<?php

namespace App\Dto;

use App\Entity\Consultation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Public payload for submitting a design-consultation request from the
 * storefront.
 */
class ConsultationRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 120)]
        public string $name = '',

        #[Assert\NotBlank]
        #[Assert\Length(max: 40)]
        public string $phone = '',

        #[Assert\NotBlank]
        #[Assert\Choice(choices: Consultation::ROOMS)]
        public string $room = '',

        #[Assert\Length(max: 2000)]
        public ?string $message = null,
    ) {
    }
}
