<?php
namespace AndreasWolf\DecisionCoverage\Service;

use Rhumsaa\Uuid\Uuid;


/**
 * A thin wrapper around the UUID generator provided by rhumsaa/uuid, to make
 * mocking the service possible.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class UuidService {

	/**
	 * Returns a UUID v4 as a string.
	 *
	 * This does not return the object, because otherwise the result could be mocked (as Uuid is a "final" class)
	 *
	 * @return string
	 */
	public function uuid4() {
		return Uuid::uuid4()->toString();
	}

}
