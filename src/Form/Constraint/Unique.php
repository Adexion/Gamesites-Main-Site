<?php

namespace App\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class Unique extends Constraint
{
    public string $message = "To jest już użyte";
    public string $class;
    public string $field;
    public string $skip;
}