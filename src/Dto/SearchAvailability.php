<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SearchAvailability
{
    #[Assert\NotNull(message: "Start date is required.")]
    public ?\DateTimeInterface $startDate = null;

    #[Assert\NotNull(message: "End date is required.")]
    #[Assert\GreaterThan(
        propertyPath: 'startDate',
        message: "End date must be after start date."
    )]
    public ?\DateTimeInterface $endDate = null;

    #[Assert\NotNull]
    #[Assert\Positive(message: "Guests must be positive.")]
    public ?int $personnes = null;
}