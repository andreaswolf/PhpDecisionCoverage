<?php
namespace AndreasWolf\DecisionCoverage\Report;

use AndreasWolf\DecisionCoverage\Coverage\FileCoverage;


interface ReportBuilder {

	public function handleFileCoverage(FileCoverage $coverage);

}
