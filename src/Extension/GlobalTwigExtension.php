<?php

namespace App\Extension;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class GlobalTwigExtension extends AbstractExtension implements GlobalsInterface
{
    private $requestStack;
    private Session $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->session = $this->requestStack->getSession();
    }

    public function getGlobals(): array
    {

        return [
            'workspace' => $this->session->get('workspace')
        ];
    }
}