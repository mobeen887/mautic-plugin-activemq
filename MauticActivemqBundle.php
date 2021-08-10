<?php

namespace MauticPlugin\MauticActivemqBundle;

use Mautic\PluginBundle\Bundle\PluginBundleBase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class MauticActivemqBundle.
 */

 class MauticActivemqBundle extends PluginBundleBase
 {  
     /**
      * {@inheritdoc}
      */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }     
 }
 