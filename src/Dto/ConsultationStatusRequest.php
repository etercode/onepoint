<?php

namespace App\Dto;

use App\Entity\Consultation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Admin payload to change a consultation's status.
 */
class ConsultationStatusRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Choice(choices: Consultation::STATUSES)]
        public string $status = '',
    ) {
    }
}
