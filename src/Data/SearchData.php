<?php

namespace App\Data;

use App\Entity\Campus;
use App\Entity\User;

class SearchData
{
    public ?string $q ='';

    public ?Campus $campus = null;

    public ?User $user = null;
    public bool $organized = false;

    public bool $registered = false;

    public bool $notRegistered = false;

    public bool $eventCompleted = false;

    public ?\DateTimeInterface $startDate = null;

    public ?\DateTimeInterface $endDate = null;

}