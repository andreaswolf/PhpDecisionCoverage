<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Weighting;


class BooleanAndWeight extends DecisionWeight {

	public function getValue() {
		return $this->getTrueValue() + $this->getFalseValue();
	}

	public function getTrueValue() {
		return $this->leftWeight->getTrueValue() * $this->rightWeight->getTrueValue(); // TT
	}

	public function getFalseValue() {
		return $this->leftWeight->getFalseValue() + // Fx
			$this->leftWeight->getTrueValue() * $this->rightWeight->getFalseValue(); // TF
	}

}
