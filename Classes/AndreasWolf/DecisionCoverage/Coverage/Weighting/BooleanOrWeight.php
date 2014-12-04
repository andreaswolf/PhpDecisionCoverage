<?php
namespace AndreasWolf\DecisionCoverage\Coverage\Weighting;


class BooleanOrWeight extends DecisionWeight {

	public function getTrueValue() {
		return $this->leftWeight->getTrueValue() // Tx
			+ $this->leftWeight->getFalseValue() * $this->rightWeight->getTrueValue(); // FT
	}

	public function getFalseValue() {
		return $this->leftWeight->getFalseValue() * $this->rightWeight->getFalseValue(); // FF
	}

}
