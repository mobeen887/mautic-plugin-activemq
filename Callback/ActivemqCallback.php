<?php

namespace MauticPlugin\MauticActivemqBundle\Callback;

use Doctrine\Common\Collections\ArrayCollection;
use Mautic\SmsBundle\Callback\CallbackInterface;
use Mautic\SmsBundle\Helper\ContactHelper;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ActivemqCallback implements CallbackInterface
{
    /**
     * @var ContactHelper
     */
    private $contactHelper;

    /**
     * ActivemqCallback constructor.
     *
     * @param ContactHelper $contactHelper
     */
    public function __construct(ContactHelper $contactHelper)
    {
        $this->contactHelper = $contactHelper;
    }

    /**
     * @return string
     */
    public function getTransportName()
    {
        return 'activemq';
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function getMessage(Request $request)
    {
        $this->validateRequest($request->request);

        return $request->get('content');
    }

    /**
     * @param Request $request
     *
     * @return ArrayCollection
     * @throws \Mautic\SmsBundle\Exception\NumberNotFoundException
     */
    public function getContacts(Request $request)
    {
        $this->validateRequest($request->request);

        $user = $request->get('user');

        return $this->contactHelper->findContactsByNumber($user);
    }

    /**
     * @param ParameterBag $request
     */
    private function validateRequest(ParameterBag $request)
    {
        if (
            !$request->get('@id', false)
            || !$request->get('user', false)
            || !$request->get('content', false)
        ) {
            throw new BadRequestHttpException();
        }
    }
}
