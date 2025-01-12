<?php
/**
 * @version     sw.build.version
 * @copyright   Copyright (C) 2014 - 2025 Sven Schultschik. All rights reserved
 * @license     GPL-3.0-or-later
 * @author      Sven Schultschik (extensions@schultschik.de)
 * @link        extensions.schultschik.de
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\WebAsset\WebAssetRegistry;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use SchuWeb\Plugin\Content\JaTeX\Extension\JaTeX;

/**
 * The plugin service provider
 * 
 * @since 2.0.0
 */
return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since    2.0.0
     */
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {

                $config  = (array) PluginHelper::getPlugin('content', 'jatex');
                $subject = $container->get(DispatcherInterface::class);
                $app     = Factory::getApplication();

                $plugin = new JaTeX($subject, $config);
                $plugin->setApplication($app);

                $wa = $container->get(WebAssetRegistry::class);
                $wa->addRegistryFile('media/plg_jatex/joomla.asset.json');

                return $plugin;
            }
        );
    }
};