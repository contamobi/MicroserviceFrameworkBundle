<?php

namespace Cmobi\MicroserviceFrameworkBundle\Routing\Loader;

use Cmobi\MicroserviceFrameworkBundle\Routing\Method;
use Cmobi\MicroserviceFrameworkBundle\Routing\MethodCollection;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;

class YamlRpcLoader extends FileLoader
{

    private static $availableKeys = [
        'method', 'type', 'defaults', 'resource'
    ];

    private $yamlParser;
    private $controllerParser;
    private $container;

    public function __construct(
        ContainerInterface $container,
        $path = null,
        ControllerNameParser $controllerNameConverser = null
    )
    {
        $this->controllerParser = $controllerNameConverser;
        $this->container = $container;

        if (is_null($controllerNameConverser)) {
            $this->controllerParser = $container->get('controller_name_converter');
        }

        if (is_null($path)) {
            $path = '%kernel.dir_src%/Resources';
        }
        $locator = new FileLocator($path);
        parent::__construct($locator);
    }

    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        if (!stream_is_local($path)) {
            throw new \InvalidArgumentException(sprintf('This is not a local file "%s".', $path));
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('File "%s" not found.', $path));
        }

        if (null === $this->yamlParser) {
            $this->yamlParser = new YamlParser();
        }

        try {
            $parsedConfig = $this->yamlParser->parse(file_get_contents($path));
        } catch (ParseException $e) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML.', $path), 0, $e);
        }

        $collection = new MethodCollection();
        $collection->addResource(new FileResource($path));

        // empty file
        if (null === $parsedConfig) {
            return $collection;
        }

        // not an array
        if (!is_array($parsedConfig)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $path));
        }

        foreach ($parsedConfig as $name => $config) {

            $this->validate($config, $name, $path);

            if (isset($config['resource'])) {
                $this->parseImport($collection, $config, $path, $file);
            } else {
                $this->parseRoute($collection, $name, $config);
            }
        }

        return $collection;
    }

    protected function parseImport(MethodCollection $collection, array $config, $path, $file)
    {
        $defaults = [];

        if (isset($config['defaults'])) {
            $defaults = $config['defaults'];
        }
        $type = null;

        if (isset($config['type'])) {
            $type = $config['type'];
        }
        $this->setCurrentDir(dirname($path));
        $resource = $config['resource'];

        if (substr($resource, 0, 1) === '@') {
            $resource = $this->container->get('kernel')->locateResource($config['resource']);
        }
        $subCollection = $this->import($resource, $type, false, $file);
        $subCollection->addDefaults($defaults);

        $collection->addCollection($subCollection);
    }

    protected function parseRoute(MethodCollection $collection, $name, array $config)
    {
        $defaults = [];

        if (isset($config['defaults'])) {
            $defaults = $config['defaults'];
        }
        $route = new Method(null, $config['method'], $defaults);

        if ($controller = $route->getDefault('_controller')) {
            try {
                $controller = $this->controllerParser->parse($controller);
            } catch (\Exception $e) {
                // unable to optimize unknown notation
            }

            $route->setDefault('_controller', $controller);
        }

        $collection->add($name, $route);
    }

    protected function validate($config, $name, $path)
    {
        if (!is_array($config)) {
            throw new \InvalidArgumentException(
                sprintf('The definition of "%s" in "%s" must be a YAML array.', $name, $path)
            );
        }

        if ($extraKeys = array_diff(array_keys($config), self::$availableKeys)) {
            throw new \InvalidArgumentException(sprintf(
                'The routing file "%s" contains unsupported keys for "%s": "%s". Expected one of: "%s".',
                $path, $name, implode('", "', $extraKeys), implode('", "', self::$availableKeys)
            ));
        }

        if (isset($config['resource']) && isset($config['method'])) {
            throw new \InvalidArgumentException(sprintf(
                'The routing file "%s" must not specify both the "resource" key and the "method" key for "%s".
                Choose between an import and a route definition.',
                $path, $name
            ));
        }

        if (!isset($config['resource']) && isset($config['type'])) {
            throw new \InvalidArgumentException(sprintf(
                'The "type" key for the route definition "%s" in "%s" is unsupported. It is only available
                 for imports in combination with the "resource" key.',
                $name, $path
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && in_array(
            pathinfo($resource, PATHINFO_EXTENSION), array('yml', 'yaml'), true
        ) && (!$type || 'yaml' === $type);
    }
}