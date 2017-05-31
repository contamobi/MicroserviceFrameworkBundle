<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder();
        $rootNode = $tree->root('microservice_framework');
        $rootNode
            ->children()
                ->scalarNode('microservice_name')->end()
            ->end();

        $rootNode
            ->children()
            ->arrayNode('logger')
            ->canBeEnabled()
            ->children()
                ->arrayNode('handlers')
                    ->canBeUnset()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')
                                ->isRequired()
                                ->treatNullLike('null')
                                ->beforeNormalization()
                                    ->always()
                                    ->then(function ($v) { return strtolower($v); })
                                ->end()
                            ->end()
                            ->arrayNode('gelf_config')
                                ->canBeUnset()
                                    ->children()
                                        ->scalarNode('tag')->end()
                                        ->scalarNode('hostname')->end()
                                        ->scalarNode('port')->defaultValue(12201)->end()
                                        ->scalarNode('chunk_size')->defaultValue(1420)->end()
                                    ->end()
                            ->end()
                            ->scalarNode('formatter')->end()
                            ->scalarNode('level')->defaultValue('DEBUG')->end()
                            ->booleanNode('bubble')->defaultTrue()->end()
                            ->scalarNode('path')->defaultValue('%kernel.logs_dir%/%kernel.environment%.log')->end()
                            ->scalarNode('file_permission')
                                ->defaultNull()
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(function ($v) {
                                        if (substr($v, 0, 1) === '0') {
                                            return octdec($v);
                                        }
                                        return (int) $v;
                                    })
                                ->end()
                            ->end()
                            ->scalarNode('filename_format')->defaultValue('{filename}-{date}')->end()
                            ->scalarNode('date_format')->defaultValue('Y-m-d')->end()
                            ->scalarNode('max_files')->defaultValue(0)->end() // rotating
                            ->scalarNode('ident')->defaultFalse()->end() // syslog
                            ->scalarNode('logopts')->defaultValue(LOG_PID)->end() // syslog
                            ->scalarNode('facility')->defaultValue('user')->end() // syslog
                            ->arrayNode('verbosity_levels') // console
                                ->beforeNormalization()
                                ->ifArray()
                                ->then(function ($v) {
                                    $map = array();
                                    $verbosities = array('VERBOSITY_QUIET', 'VERBOSITY_NORMAL', 'VERBOSITY_VERBOSE', 'VERBOSITY_VERY_VERBOSE', 'VERBOSITY_DEBUG');
                                    // allow numeric indexed array with ascendning verbosity and lowercase names of the constants
                                    foreach ($v as $verbosity => $level) {
                                        if (is_int($verbosity) && isset($verbosities[$verbosity])) {
                                            $map[$verbosities[$verbosity]] = strtoupper($level);
                                        } else {
                                            $map[strtoupper($verbosity)] = strtoupper($level);
                                        }
                                    }
                                    return $map;
                                })
                                ->end()
                                ->children()
                                    ->scalarNode('VERBOSITY_QUIET')->defaultValue('ERROR')->end()
                                    ->scalarNode('VERBOSITY_NORMAL')->defaultValue('WARNING')->end()
                                    ->scalarNode('VERBOSITY_VERBOSE')->defaultValue('NOTICE')->end()
                                    ->scalarNode('VERBOSITY_VERY_VERBOSE')->defaultValue('INFO')->end()
                                    ->scalarNode('VERBOSITY_DEBUG')->defaultValue('DEBUG')->end()
                                ->end()
                                ->validate()
                                ->always(function ($v) {
                                    $map = array();
                                    foreach ($v as $verbosity => $level) {
                                        $verbosityConstant = 'Symfony\Component\Console\Output\OutputInterface::'.$verbosity;
                                        if (!defined($verbosityConstant)) {
                                            throw new InvalidConfigurationException(sprintf(
                                                'The configured verbosity "%s" is invalid as it is not defined in Symfony\Component\Console\Output\OutputInterface.',
                                                $verbosity
                                            ));
                                        }
                                        if (!is_numeric($level)) {
                                            $levelConstant = 'Monolog\Logger::'.$level;
                                            if (!defined($levelConstant)) {
                                                throw new InvalidConfigurationException(sprintf(
                                                    'The configured minimum log level "%s" for verbosity "%s" is invalid as it is not defined in Monolog\Logger.',
                                                    $level, $verbosity
                                                ));
                                            }
                                            $level = constant($levelConstant);
                                        } else {
                                            $level = (int) $level;
                                        }
                                        $map[constant($verbosityConstant)] = $level;
                                    }
                                    return $map;
                                })
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->end()
        ->end();

        $rootNode->fixXmlConfig('rabbitmq_connection')
            ->children()
                ->arrayNode('rabbitmq_connections')
                    ->useAttributeAsKey('key')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')->defaultValue('localhost')->end()
                            ->scalarNode('port')->defaultValue(5672)->end()
                            ->scalarNode('user')->defaultValue('guest')->end()
                            ->scalarNode('password')->defaultValue('guest')->end()
                            ->scalarNode('vhost')->defaultValue('/')->end()
                            ->booleanNode('lazy')->defaultFalse()->end()
                            ->scalarNode('connection_timeout')->defaultValue(3)->end()
                            ->scalarNode('read_write_timeout')->defaultValue(3)->end()
                            ->arrayNode('ssl_context')
                                ->useAttributeAsKey('key')
                                ->canBeUnset()
                                ->prototype('variable')->end()
                            ->end()
                            ->booleanNode('keepalive')->defaultFalse()->info('requires php-amqplib v2.4.1+ and PHP5.4+')->end()
                            ->scalarNode('heartbeat')->defaultValue(0)->info('requires php-amqplib v2.4.1+')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        $rootNode->fixXmlConfig('rpc_server')
            ->children()
                ->arrayNode('rpc_servers')
                ->useAttributeAsKey('key')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('queue')
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->scalarNode('connection')->defaultValue('default')->end()
                                    ->scalarNode('basic_qos')->defaultValue(1)->end()
                                    ->booleanNode('durable')->defaultFalse()->end()
                                    ->booleanNode('auto_delete')->defaultTrue()->end()
                                    ->variableNode('arguments')->defaultValue([])->end()
                                ->end()
                            ->end()
                            ->scalarNode('service')->defaultValue('cmobi_msf.message.handler')->end()
                            ->scalarNode('jobs')->defaultValue(1)->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        $rootNode->fixXmlConfig('worker')
            ->children()
                ->arrayNode('workers')
                ->useAttributeAsKey('key')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('queue')
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->scalarNode('connection')->defaultValue('default')->end()
                                    ->scalarNode('basic_qos')->defaultValue(1)->end()
                                    ->variableNode('arguments')->defaultValue([])->end()
                                ->end()
                            ->end()
                            ->scalarNode('service')->defaultValue('cmobi_msf.message.handler')->end()
                            ->scalarNode('jobs')->defaultValue(1)->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        $rootNode->fixXmlConfig('subscriber')
            ->children()
                ->arrayNode('subscribers')
                ->useAttributeAsKey('key')
                    ->prototype('array')
                    ->children()
                        ->arrayNode('queue')
                            ->children()
                                ->scalarNode('name')->end()
                                ->scalarNode('exchange')->end()
                                ->scalarNode('connection')->defaultValue('default')->end()
                                ->scalarNode('basic_qos')->defaultValue(1)->end()
                                ->scalarNode('exchange_type')->defaultValue('topic')->end()
                                ->variableNode('arguments')->defaultValue([])->end()
                            ->end()
                        ->end()
                        ->scalarNode('service')->defaultValue('cmobi_msf.message.handler')->end()
                        ->scalarNode('jobs')->defaultValue(1)->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $tree;
    }
}