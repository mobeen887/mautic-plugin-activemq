<?php

return [
    'name'        => 'MauticAMQ',
    'description' => 'Enables integrations Mautic AMQ Transport',
    'version'     => '1.0',
    'author'      => 'Mautic',
    'services'    => [
        'events'       => [
        ],
        'forms'        => [
            'mautic.activemq.form.config_auth' => [
                'class' => \MauticPlugin\MauticActivemqBundle\Form\Type\ConfigAuthType::class,
                'arguments' => [
                    'mautic.lead.model.field',
                ],
            ],
        ],
        'helpers'      => [
            'mautic.activemq.message_factory' => [
                'class' => 'MauticPlugin\MauticActivemqBundle\Message\MessageFactory',
                'alias' => 'activemq_message_factory',
            ],
        ],
        'other'        => [
            'mautic.sms.transport.activemq' => [
                'class'        => \MauticPlugin\MauticActivemqBundle\Transport\ActivemqTransport::class,
                'arguments'    => [
                    'mautic.integrations.helper',
                    'monolog.logger.mautic',
                    'mautic.activemq.connector',
                    'mautic.activemq.message_factory',
                    'mautic.lead.model.dnc',
                ],
                'tag'          => 'mautic.sms_transport',
                'tagArguments' => [
                    'integrationAlias' => 'Activemq',
                ],
            ],
            'mautic.sms.activemq.callback' => [
                'class' => \MauticPlugin\MauticActivemqBundle\Callback\ActivemqCallback::class,
                'arguments' => [
                    'mautic.sms.helper.contact',
                ],
                'tag' => 'mautic.sms_callback_handler',
            ],
            'mautic.activemq.connector'     => [
                'class'     => \MauticPlugin\MauticActivemqBundle\Activemq\Connector::class,
                'arguments' => [
                    'mautic.helper.phone_number',
                    'mautic.helper.integration',
                    'monolog.logger.mautic',
                ],
            ],
        ],
        'models'       => [
        ],
        'integrations' => [
            'mautic.integration.activemq' => [
                'class'     => \MauticPlugin\MauticActivemqBundle\Integration\ActivemqIntegration::class,
                'arguments' => [
                ],
                'tags'      => [
                    'mautic.integration',
                    'mautic.basic_integration',
                    'mautic.config_integration',
                    'mautic.auth_integration',
                ],
            ],
        ],
    ],
    'routes'      => [
        'main'   => [
        ],
        'public' => [
        ],
        'api'    => [
        ],
    ],
    'menu'        => [
        'main' => [
            'items' => [
                'mautic.sms.smses' => [
                    'route'    => 'mautic_sms_index',
                    'access'   => ['sms:smses:viewown', 'sms:smses:viewother'],
                    'parent'   => 'mautic.core.channels',
                    'checks'   => [
                        'integration' => [
                            'Activemq' => [
                                'enabled' => true,
                            ],
                        ],
                    ],
                    'priority' => 70,
                ],
            ],
        ],
    ],
    'parameters'  => [
    ],
];
