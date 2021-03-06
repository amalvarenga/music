<?php

/**
 * ownCloud - Music app
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Pauli Järvinen <pauli.jarvinen@gmail.com>
 * @copyright Morris Jobke 2013, 2014
 * @copyright Pauli Järvinen 2017 - 2020
 */

namespace OCA\Music\Db;

use OCP\IL10N;
use \OCP\IURLGenerator;

use \OCP\AppFramework\Db\Entity;

/**
 * @method string getName()
 * @method setName(string $name)
 * @method string getImage()
 * @method setImage(string $image)
 * @method string getUserId()
 * @method setUserId(string $userId)
 * @method string getMbid()
 * @method setMbid(string $mbid)
 * @method string getHash()
 * @method setHash(string $hash)
 */
class Artist extends Entity {
	public $name;
	public $image; // URL
	public $userId;
	public $mbid;
	public $hash;

	public function getUri(IURLGenerator $urlGenerator) {
		return $urlGenerator->linkToRoute(
			'music.api.artist',
			['artistIdOrSlug' => $this->id]
		);
	}

	public function getNameString(IL10N $l10n) {
		$name = $this->getName();
		if ($name === null) {
			$name = $l10n->t('Unknown artist');
			if (!\is_string($name)) {
				/** @var \OC_L10N_String $name */
				$name = $name->__toString();
			}
		}
		return $name;
	}

	/**
	 * Get initial character of the artist name in upper case.
	 * This is intended to be used as index in a list of aritsts.
	 */
	public function getIndexingChar() {
		// For unknown artists, use '?'
		$char = '?';
		$name = $this->getName();

		if (!empty($name)) {
			$char = \mb_convert_case(\mb_substr($name, 0, 1), MB_CASE_UPPER);
		}
		// Bundle all numeric characters together
		if (\is_numeric($char)) {
			$char = '#';
		}

		return $char;
	}

	/**
	 * @param IL10N $l10n
	 * @param array $albums in the "toCollection" format
	 * @return array
	 */
	public function toCollection(IL10N $l10n, $albums) {
		return [
			'id' => $this->getId(),
			'name' => $this->getNameString($l10n),
			'albums' => $albums
		];
	}

	public function toAPI(IURLGenerator $urlGenerator, $l10n) {
		return [
			'id' => $this->getId(),
			'name' => $this->getNameString($l10n),
			'image' => $this->getImage(),
			'slug' => $this->getId() . '-' . $this->slugify('name'),
			'uri' => $this->getUri($urlGenerator)
		];
	}
}
