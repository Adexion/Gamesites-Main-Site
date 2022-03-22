<?php

namespace App\Service;

use App\Repository\OrderRepository;

class RandomCouponGenerator
{
    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function generate(): string
    {
        $val = '';
        $last = $this->orderRepository->findOneBy([], ['id' => 'DESC']);

        for ($i = 0; $i < 7 - (strlen($last->getId() ?? 0)); $i++) {
            $val .= rand(0, 9);
        }

        return 'GS' . (($last->getId() ?? 0) + 1) . 'A' . date('ym') . $val;
    }
}