<?php

namespace app\models;

class LoanProcessingParams
{
    public function __construct(
        public readonly int $delay,
    ) {}
}