<?php

use Etu\CoreBundle\Framework\Definition\Module;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Exception\FatalErrorException;
use Symfony\Component\Config\Loader\LoaderInterface;


/**
 * EtuUTT AppKernel. Redefine the way to load bundles for the modules system.
 */
class AppKernel extends Kernel
{
	/**
	 * @var array
	 */
	protected $modules;

	/**
	 * Register the bundles (and by the way the modules).
	 *
	 * @return array|Symfony\Component\HttpKernel\Bundle\BundleInterface[]
	 * @throws Symfony\Component\HttpKernel\Exception\FatalErrorException
	 */
	public function registerBundles()
    {
	    /*
	     * Basic bundles, required to load the rest
	     */
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            // new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new Etu\Core\CoreBundle\EtuCoreBundle(),
            new Etu\Core\UserBundle\EtuUserBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

	    /*
	     * Modules bundles, loaded dynamically using the app/config/modules.yml
	     *
	     * @todo Remove this strict dependency to Yaml
	     */
	    $modules = Symfony\Component\Yaml\Yaml::parse($this->getRootDir().'/config/modules.yml');

	    if (isset($modules['modules'])) {
		    if (! is_array($modules['modules'])) {
			    throw new FatalErrorException(sprintf('Key "modules" in app/config/modules.yml must be an array'));
		    }

		    foreach ($modules['modules'] as $module) {
			    if (file_exists($this->getRootDir().'/../src/'.str_replace('\\', '/', $module).'.php')) {
				    require $this->getRootDir().'/../src/'.str_replace('\\', '/', $module).'.php';
			    }

			    if (class_exists($module, false)) {
				    $module = new ReflectionClass($module);
				    $module = $module->newInstance();

					if ($module instanceof Module) {
						$bundles[] = $module;
						$this->registerModuleDefinition($module);
					}
			    }
		    }
	    }

        return $bundles;
    }

	/**
	 * Register the configuration.
	 *
	 * @param Symfony\Component\Config\Loader\LoaderInterface $loader
	 */
	public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

	/**
	 * @param Etu\CoreBundle\Framework\Definition\Module $module
	 * @return AppKernel
	 */
	public function registerModuleDefinition(Module $module)
	{
		$this->modules[$module->getName()] = $module;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getModulesDefinitions()
	{
		return $this->modules;
	}
}
