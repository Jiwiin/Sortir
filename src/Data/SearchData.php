<?php

namespace App\Data;

class SearchData
{
    public string $q ='';

    public array $campus = [];

    public bool $organized = false;

    public bool $registered = false;

    public bool $notRegistered = false;

    public bool $eventCompleted = false;

    public ?\DateTime $startDate = null;

    public ?\DateTime $endDate = null;

}