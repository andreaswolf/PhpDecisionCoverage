<?php
namespace AndreasWolf\DecisionCoverage\StaticAnalysis;

use AndreasWolf\DecisionCoverage\Source\SourceFile;
use AndreasWolf\DecisionCoverage\StaticAnalysis\Persistence\SerializedObjectMapper;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Instrumenter;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator\MethodEntryProbeFactory;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator\ProbeFactory;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\Manipulator\NodeIdGenerator;
use AndreasWolf\DecisionCoverage\StaticAnalysis\SyntaxTree\SyntaxTree;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class FileAnalyzer {

	/**
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;

	/**
	 * @var LoggerInterface
	 */
	protected $logger;


	public function __construct(EventDispatcherInterface $eventDispatcher, LoggerInterface $logger = NULL) {
		if (!$logger) {
			$logger = new NullLogger();
		}

		$this->eventDispatcher = $eventDispatcher;
		$this->logger = $logger;
	}

	/**
	 * @param SourceFile $file
	 * @return FileResult
	 */
	public function analyzeFile(SourceFile $file) {
		$nodes = $file->getTopLevelStatements();
		$result = new FileResult($file->getFilePath(), new SyntaxTree($nodes));

		// FIXME we should use the global event dispatcher instance here; what currently prevents this is that the visitors are not
		// cleanly removed after the instrumentation (and therefore each file after the first one gets instrumented multiple times)
		$eventDispatcher = new EventDispatcher();
		$instrumenter = new Instrumenter($eventDispatcher, $this->logger);
		$instrumenter->addVisitor(new NodeIdGenerator(), 0);
		$instrumenter->addVisitor(new ProbeFactory($result), 1);
		$instrumenter->addVisitor(new MethodEntryProbeFactory($result, $this->logger), 2);
		$instrumenter->instrument($nodes);

		return $result;
	}

	public function analyzeFolder($folder) {
		if (!file_exists($folder) || !is_dir($folder)) {
			throw new \InvalidArgumentException($folder . ' does not exist or is no folder.', 1413747411);
		}

		$fileIterator = $this->getDirectoryIterator($folder);

		$parser = new \PhpParser\Parser(
			new \PhpParser\Lexer(
				array('usedAttributes' => array(
					'comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos'
				))
			)
		);
		$resultSet = new ResultSet();
		foreach ($fileIterator as $file) {
			$sourceFile = new SourceFile($file->getPathname());
			$sourceFile->setParser($parser);
			$fileResult = $this->analyzeFile($sourceFile);

			$resultSet->addFileResult($fileResult);
		}

		return $resultSet;
	}

	/**
	 * @param $folder
	 * @return \RecursiveIteratorIterator
	 */
	protected function getDirectoryIterator($folder) {
		$directoryIterator = new \RecursiveDirectoryIterator(realpath($folder));
		$fileIterator = new \RecursiveIteratorIterator(new \RecursiveCallbackFilterIterator($directoryIterator,
			function ($current, $key, $iterator) {
				/** @var $iterator \RecursiveIterator */
				/** @var $current \DirectoryIterator */
				// Allow recursion
				if ($iterator->hasChildren()) {
					return TRUE;
				}
				// Check for large file
				if ($current->isFile() && substr($current->getFilename(), -4) == '.php') {
					return TRUE;
				}

				return FALSE;
			}
		));

		return $fileIterator;
	}

	/**
	 * Writes the results to the given file.
	 *
	 * @param string $file
	 * @param ResultSet $result
	 * @deprecated Directly use a DataMapper instance
	 */
	public function writeAnalysisResultsToFile($file, ResultSet $result) {
		$mapper = new SerializedObjectMapper();

		$mapper->saveToFile($file, $result);
	}

}
