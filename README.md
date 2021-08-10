# mautic-plugin-activemq

Activemq Transport plugin for Mautic 3.2

Plugin to provide Activemq transport to Mautic.

How to Install

Preparations:
In Mautic, create a contact custom field to hold the Activemq Keyword associated with Activemq User.

Installation (do not use composer at this time)

1. Download https://github.com/mobeen887/mautic-plugin-activemq/archive/master.zip
2. Extract it to plugins/MauticActivemqBundle
3. Delete `app/cache/prod` or clear cache using `rm -rf var/cache/*`
4. Run php app/console mautic:plugins:install 
5. Go to Plugins in Mautic's admin menu (/s/plugins)
6. Click on MauticAMQ, publish, configure it with the requested information including selecting the custom field created above
7. Go to Mautic's Configuration (/s/config/edit), click on the Text Message Settings, then choose Activemq as the default transport.