<?php

namespace App\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class Order extends Constraint
{
    public string $message = 'Nie znaleźliśmy podanego kuponu, bądź jest on już nie ważny';
    public string $messageUsed = 'Kupon został już wykorzystany';
}