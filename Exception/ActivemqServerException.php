<?php

namespace MauticPlugin\MauticActivemqBundle\Exception;

/**
 * Class ActivemqServerException.
 */
class ActivemqServerException extends ActivemqPluginException
{
    /**
     * @var null|string
     */
    private $payload;

    /**
     * ActivemqServerException constructor.
     *
     * @param string      $xmlResponse
     * @param int         $httpCode
     * @param null|string $payload
     */
    public function __construct(string $xmlResponse, int $httpCode, string $payload = null)
    {
        $this->payload = $payload;

        $message = sprintf('%s (%d)', $xmlResponse, $httpCode);

        parent::__construct($message, $httpCode);
    }

    /**
     * @return string|null
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
