<?php
namespace AndreasWolf\DecisionCoverage\Core;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;


class Bootstrap {

	/**
	 * The singleton instance of this class
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * @var ContainerBuilder
	 */
	protected $dependencyInjectionContainer;

	private function __construct() {
	}

	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @return \Symfony\Component\DependencyInjection\ContainerBuilder
	 */
	public function getDependencyInjectionContainer() {
		return $this->dependencyInjectionContainer;
	}

	public function run() {
		$this->setupAutoloader();
		$this->setupDependencyInjection();
	}

	public function setupAutoloader() {
		foreach (array(__DIR__ . '/../../../../../../autoload.php', __DIR__ . '/../../../../vendor/autoload.php') as $file) {
			if (file_exists($file)) {
				$composerAutoloaderFile = $file;
				break;
			}
		}

		require $composerAutoloaderFile;
	}

	/**
	 *
	 *
	 * @return void
	 */
	public function setupDependencyInjection() {
		// configure dependency injection
		$this->dependencyInjectionContainer = new ContainerBuilder();
		$loader = new YamlFileLoader($this->dependencyInjectionContainer, new FileLocator(__DIR__ . '/../../../../'));
		$loader->load('Configuration/Services.yml');

		$this->dependencyInjectionContainer->compile();
	}

}

$bootstrap = Bootstrap::getInstance();
$bootstrap->run();