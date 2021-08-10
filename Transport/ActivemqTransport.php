<?php

declare(strict_types=1);

namespace MauticPlugin\MauticActivemqBundle\Transport;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\DoNotContact;
use Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException;
use Mautic\IntegrationsBundle\Helper\IntegrationsHelper;
use Mautic\SmsBundle\Sms\TransportInterface;
use MauticPlugin\MauticActivemqBundle\Exception\InvalidRecipientException;
use MauticPlugin\MauticActivemqBundle\Exception\MessageException;
use MauticPlugin\MauticActivemqBundle\Exception\ActivemqPluginException;
use MauticPlugin\MauticActivemqBundle\Exception\ActivemqServerException;
use MauticPlugin\MauticActivemqBundle\Integration\ActivemqIntegration;
// use MauticPlugin\MauticActivemqBundle\Stomp\StompFrame;
// use MauticPlugin\MauticActivemqBundle\Stomp\StompMessage;
// use MauticPlugin\MauticActivemqBundle\Stomp\Validator\MessageContentValidator;
use MauticPlugin\MauticActivemqBundle\src\MyCustomMessage;
use Monolog\Logger;

/**
 * Class ActivemqTransport is the transport service for mautic.
 */
class ActivemqTransport implements TransportInterface
{
    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var string
     */
    private $keywordField;

    /**
     * @var DoNotContact
     */
    private $doNotContactService;

    /**
     * @var IntegrationsHelper
     */
    private $integrationsHelper;

    /**
     * @var bool
     */
    private $connectorConfigured = false;

    /**
     * ActivemqTransport constructor.
     *
     * @param IntegrationsHelper $integrationsHelper
     * @param Logger            $logger
     * @param Connector         $connector
     * @param MessageFactory    $messageFactory
     * @param DoNotContact      $doNotContactService
     */
    public function __construct(
        IntegrationsHelper $integrationsHelper,
        Logger $logger,
        Connector $connector,
        MessageFactory $messageFactory,
        DoNotContact $doNotContactService
    ) {
        $this->logger              = $logger;
        $this->connector           = $connector;
        $this->messageFactory      = $messageFactory;
        $this->doNotContactService = $doNotContactService;
        $this->integrationsHelper  = $integrationsHelper;
    }

    /**
     * @param Lead   $contact
     * @param string $content
     *
     * @return bool|PluginNotConfiguredException|mixed|string
     * @throws MessageException
     * @throws ActivemqPluginException
     * @throws \Mautic\IntegrationsBundle\Exception\IntegrationNotFoundException
     */
    public function sendSms(Lead $contact, $content)
    {
        $number = $contact->getLeadPhoneNumber();
        if (empty($number)) {
            return false;
        }

        $util = PhoneNumberUtil::getInstance();

        if (is_null($this->connector)) {
            throw new ActivemqPluginException('There is no connector available');
        }

        if (!$this->connectorConfigured && !$this->configureConnector()) {
            return new PluginNotConfiguredException();
        }

        /** @var StompMessage $message */
        $message = $this->messageFactory->create();

        $message
            ->setContent($content)
            ->setKeyword($contact->getFieldValue($this->keywordField));

        try {
            $parsed = $util->parse($number, 'US');
            $number = $util->format($parsed, PhoneNumberFormat::E164);
            $number = substr($number, 1);
            $message->setUserId($number);

            MessageContentValidator::validate($message);
            $this->connector->sendMtMessage($message);
        } catch (NumberParseException $exception) {
            $this->logger->addInfo('Invalid number format', ['error' => $exception->getMessage()]);

            return 'mautic.activemq.failed.invalid_phone_number';
        } catch (InvalidRecipientException $exception) {    // There is something with the user, probably opt-out
            $this->logger->addInfo(
                'Invalid recipient',
                ['error' => $exception->getMessage(), 'number' => $number, 'keyword' => $message->getKeyword(), 'payload' => $exception->getPayload()]
            );

            $this->unsubscribeInvalidUser($contact, $exception);

            return 'mautic.activemq.failed.rejected_recipient';
        } catch (MessageException $exception) {  // Message contains invalid characters or is too long
            $this->logger->addError(
                'Invalid message.',
                ['error' => $exception->getMessage(), 'number' => $number, 'keyword' => $message->getKeyword()]
            );

            return 'mautic.activemq.failed.invalid_message_format';
        } catch (ActivemqServerException $exception) {
            $this->logger->addError(
                'Server response error.',
                ['error' => $exception->getMessage(), 'number' => $number, 'keyword' => $message->getKeyword(), 'payload' => $exception->getPayload()]
            );

            return $exception->getMessage();
        } catch (ActivemqPluginException $exception) {
            $this->logger->addError(
                'Activemq plugin unhandled exception',
                ['error' => $exception->getMessage(), 'number' => $number, 'keyword' => $message->getKeyword()]
            );

            throw $exception;
        }

        return true;
    }

    /**
     * Add user to DNC.
     *
     * @param Lead       $contact
     * @param \Exception $exception
     */
    private function unsubscribeInvalidUser(Lead $contact, \Exception $exception)
    {
        $this->logger->addWarning(
            'Invalid user added to DNC list. '.$exception->getMessage(),
            ['exception' => $exception]
        );

        $this->doNotContactService->addDncForContact(
            $contact->getId(),
            'sms',
            \Mautic\LeadBundle\Entity\DoNotContact::UNSUBSCRIBED,
            $exception->getMessage(),
            true
        );
    }

    /**
     * @return bool
     * @throws \Mautic\IntegrationsBundle\Exception\IntegrationNotFoundException
     */
    private function configureConnector()
    {
        $integration              = $this->integrationsHelper->getIntegration(ActivemqIntegration::NAME);
        $integrationConfiguration = $integration->getIntegrationConfiguration();

        if ($integrationConfiguration->getIsPublished()) {
            $keys = $integrationConfiguration->getApiKeys();

            if (isset($keys['username']) && isset($keys['password']) && isset($keys['activemq_url'])) {
                $this->connector
                    ->setActivemqUrl($keys['activemq_url'])
                    ->setPartnerId($keys['username'])
                    ->setPassword($keys['password']);

                $this->keywordField = isset($keys['keyword_field']) ? $keys['keyword_field'] : null;

                $this->connectorConfigured = true;

                return true;
            }
        }

        return false;
    }
}
