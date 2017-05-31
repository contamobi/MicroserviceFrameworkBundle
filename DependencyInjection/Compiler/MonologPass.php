<?php

namespace Cmobi\MicroserviceFrameworkBundle\DependencyInjection\Compiler;

use Cmobi\MicroserviceFrameworkBundle\Exception\MicroserviceException;
use Cmobi\MicroserviceFrameworkBundle\Logger\GelfLogger;
use Cmobi\MicroserviceFrameworkBundle\Logger\LoggerService;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class MonologPass implements CompilerPassInterface
{
    private $handlers;

    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**[
     * @param ContainerBuilder $container
     * @throws MicroserviceException
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($this->handlers as $name => $handler) {
            if ($handler['type'] === 'gelf') {
                $id = $this->buildPublisher($container, $handler);
                $definition = new Definition(GelfLogger::class, [
                    'tag' => $handler['gelf_config']['tag'],
                    'publisher' => new Reference($id)

                ]);
            } else {
                $id = $this->buildHandler($container, $name, $handler);
                $definition = new Definition(Logger::class, [
                    'name' => $name,
                    'handlers' => [new Reference($id)]
                ]);
            }
            $container->setDefinition(sprintf('cmobi_msf.logger.%s', $name), $definition);

            if ($name === 'default') {
                $definition = new Definition(LoggerService::class, [
                    'logger' => new Reference(sprintf('cmobi_msf.logger.%s', $name))
                ]);
                $container->setDefinition('cmobi_msf.logger', $definition);
            }

            if (! $container->has('cmobi_msf.logger')) {
                throw new MicroserviceException(
                    'logger error, handler "default" not found.',
                    0,
                    __METHOD__,
                    __LINE__
                );
            }
        }
    }

    private function getHandlerId($name)
    {
        return sprintf('cmobi_msf.logger.handler.%s', $name);
    }

    private function levelToMonologConst($level)
    {
        return is_int($level) ? $level : constant('Monolog\Logger::'.strtoupper($level));
    }

    private function buildPublisher(ContainerBuilder $container, array $handler)
    {
        if (class_exists('Gelf\Transport\UdpTransport')) {
            $transport = new Definition("Gelf\Transport\UdpTransport", [
                $handler['gelf_config']['hostname'],
                $handler['gelf_config']['port'],
                $handler['gelf_config']['chunk_size'],
            ]);
            $transportId = uniqid('monolog.gelf.transport.', true);
            $transport->setPublic(false);
            $container->setDefinition($transportId, $transport);

            $publisher = new Definition('Gelf\Publisher', []);
            $publisher->addMethodCall('addTransport', array(new Reference($transportId)));
            $publisherId = uniqid('monolog.gelf.publisher.', true);
            $publisher->setPublic(false);
            $container->setDefinition($publisherId, $publisher);
        } elseif (class_exists('Gelf\MessagePublisher')) {
            $publisher = new Definition('Gelf\MessagePublisher', [
                $handler['publisher']['hostname'],
                $handler['publisher']['port'],
                $handler['publisher']['chunk_size'],
            ]);
            $publisherId = uniqid('monolog.gelf.publisher.', true);
            $publisher->setPublic(false);
            $container->setDefinition($publisherId, $publisher);
        } else {
            throw new \RuntimeException('The gelf handler requires the graylog2/gelf-php package to be installed');
        }

        return $publisherId;
    }

    private function buildHandler(ContainerBuilder $container, $name, array $handler)
    {
        $handlerId = $this->getHandlerId($name);
        if ('service' === $handler['type']) {
            $container->setAlias($handlerId, $handler['id']);
            return $handlerId;
        }
        $definition = new Definition($this->getHandlerClassByType($handler['type']));
        $handler['level'] = $this->levelToMonologConst($handler['level']);

        switch ($handler['type']) {
            case 'stream':
                $definition->setArguments([
                    $handler['path'],
                    $handler['level'],
                    $handler['bubble'],
                    $handler['file_permission'],
                ]);
                break;
            case 'console':
                $definition->setArguments([
                    null,
                    $handler['bubble'],
                    isset($handler['verbosity_levels']) ? $handler['verbosity_levels'] : [],
                ]);
                $definition->addTag('kernel.event_subscriber');
                break;
            case 'rotating_file':
                $definition->setArguments([
                    $handler['path'],
                    $handler['max_files'],
                    $handler['level'],
                    $handler['bubble'],
                    $handler['file_permission'],
                ]);
                $definition->addMethodCall('setFilenameFormat', [
                    $handler['filename_format'],
                    $handler['date_format'],
                ]);
                break;
            case 'syslog':
                $definition->setArguments([
                    $handler['ident'],
                    $handler['facility'],
                    $handler['level'],
                    $handler['bubble'],
                    $handler['logopts'],
                ]);
                break;
            case 'stdout':
                break;
            default:
                throw new \InvalidArgumentException(sprintf(
                    'Invalid handler type "%s" given for handler "%s"',
                    $handler['type'], $name
                ));
        }
        if (!empty($handler['formatter'])) {
            $definition->addMethodCall('setFormatter', [new Reference($handler['formatter'])]);
        }
        $container->setDefinition($handlerId, $definition);
        return $handlerId;
    }

    private function getHandlerClassByType($handlerType)
    {
        $typeToClassMapping = [
            'stream' => 'Monolog\Handler\StreamHandler',
            'console' => 'Symfony\Bridge\Monolog\Handler\ConsoleHandler',
            'rotating_file' => 'Monolog\Handler\RotatingFileHandler',
            'syslog' => 'Monolog\Handler\SyslogHandler',
            'gelf' => 'Monolog\Handler\GelfHandler',
            'stdout' => 'Cmobi\MicroserviceFrameworkBundle\Logger\StdoutHandler'
        ];
        if (! isset($typeToClassMapping[$handlerType])) {
            throw new \InvalidArgumentException(sprintf(
                'There is no handler class defined for handler "%s".',
                $handlerType
            ));
        }
        return $typeToClassMapping[$handlerType];
    }
}