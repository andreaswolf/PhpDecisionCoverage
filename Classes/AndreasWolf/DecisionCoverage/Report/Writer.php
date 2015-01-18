<?php
namespace AndreasWolf\DecisionCoverage\Report;

use AndreasWolf\DecisionCoverage\Report\Html\SourceFile;


interface Writer {


	public function writeReportForSourceFile(SourceFile $file);

}