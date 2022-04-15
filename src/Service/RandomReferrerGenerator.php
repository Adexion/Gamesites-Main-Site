<?php

namespace App\Service;

use App\Repository\UserReferrerRepository;

class RandomReferrerGenerator
{
    private UserReferrerRepository $referrerRepository;

    public function __construct(UserReferrerRepository $referrerRepository)
    {
        $this->referrerRepository = $referrerRepository;
    }

    public function generate(): string
    {
        $val = '';
        $last = $this->referrerRepository->findOneBy([], ['id' => 'DESC']);
        for ($i = 0; $i < 7 - (strlen($last ? $last->getId() : 0)); $i++) {
            $val .= rand(0, 9);
        }

        return 'GS' . (($last ? $last->getId() : 0)) . 'A' . date('ym') . $val;
    }
}