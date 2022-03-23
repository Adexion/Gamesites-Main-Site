<?php

namespace App\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class Domain extends Constraint
{
    public string $message = 'Twoja domena nie została przekierowana na IP {{ ip }}. Sprawdź DNS i spróbuj ponownie.' ;
    public string $messageExist = 'Twoja domena została już zajerestrowana';
}