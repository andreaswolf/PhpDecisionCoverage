<?php
namespace AndreasWolf\DecisionCoverage\Tests\Unit\Config;

use AndreasWolf\DecisionCoverage\Tests\Unit\UnitTestCase;
use Symfony\Component\Filesystem\Filesystem;


class RelativePathsTest extends UnitTestCase {
	use ConfigLoaderTrait;

	protected $rootDir;

	protected $mockedDirectories = [];

	protected $testDir;
	protected $configDir;
	protected $sourceDir;
	protected $workDir;
	protected $reportsDir;


	protected function setUp() {
		parent::setUp();

		$this->rootDir = realpath(__DIR__ . '/../../../');
	}

	protected function tearDown() {
		$fs = new Filesystem();
		$fs->remove($this->mockedDirectories);
	}

	/**
	 * @test
	 */
	public function pathBelowConfigurationFileIsResolvedCorrectly() {
		$this->setUpTestEnvironmentWithFixture();

		$config = $this->loadProjectConfiguration($this->configDir . '/RelativePaths.xml');

		$path = $config->getSourceDirectory();

		$this->assertEquals($this->sourceDir, $path->getRealPath());
	}

	/**
	 * Creates a mocked directory structure for the test paths in RelativePaths.xml
	 *
	 * @return void
	 */
	protected function setUpTestEnvironmentWithFixture() {
		$fs = new Filesystem();
		$this->testDir = sys_get_temp_dir() . '/' . md5((string)microtime(TRUE));
		$this->configDir = $this->testDir . '/project/dir/';
		$this->reportsDir = $this->testDir . '/report';
		$this->workDir = $this->testDir . '/project/coverage/workDir';
		$this->sourceDir = $this->testDir . '/project/dir/projectSource';

		$fs->mkdir(array($this->testDir, $this->reportsDir, $this->workDir, $this->sourceDir));

		$fs->copy($this->rootDir . '/Tests/Fixtures/Configuration/RelativePaths.xml', $this->configDir . '/RelativePaths.xml');
		$this->mockedDirectories[] = $this->testDir;
	}

}
