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
 * @copyright Pauli Järvinen 2016 - 2020
 */

namespace OCA\Music\Db;

use \OCP\IURLGenerator;

use \OCP\AppFramework\Db\Entity;

use \OCA\Music\Utility\Util;

/**
 * @method string getTitle()
 * @method setTitle(string $title)
 * @method int getNumber()
 * @method setNumber(int $number)
 * @method int getDisk()
 * @method setDisk(int $disk)
 * @method int getYear()
 * @method setYear(int $year)
 * @method int getArtistId()
 * @method setArtistId(int $artistId)
 * @method Artist getArtist()
 * @method setArtist(Artist $artist)
 * @method int getAlbumId()
 * @method setAlbumId(int $albumId)
 * @method Album getAlbum()
 * @method setAlbum(Album $album)
 * @method int getLength()
 * @method setLength(int $length)
 * @method int getFileId()
 * @method setFileId(int $fileId)
 * @method int getBitrate()
 * @method setBitrate(int $bitrate)
 * @method string getMimetype()
 * @method setMimetype(string $mimetype)
 * @method string getUserId()
 * @method setUserId(string $userId)
 * @method string getFilename()
 * @method setFilename(string $filename)
 * @method int getSize()
 * @method setSize(int $size)
 */
class Track extends Entity {
	public $title;
	public $number;
	public $disk;
	public $year;
	public $artistId;
	public $albumId;
	public $length;
	public $fileId;
	public $bitrate;
	public $uri;
	public $mimetype;
	public $userId;
	public $mbid;
	public $filename;
	public $size;

	// these don't come from the music_tracks table:
	public $artist;
	public $album;

	public function __construct() {
		$this->addType('number', 'int');
		$this->addType('disk', 'int');
		$this->addType('year', 'int');
		$this->addType('artistId', 'int');
		$this->addType('albumId', 'int');
		$this->addType('length', 'int');
		$this->addType('bitrate', 'int');
		$this->addType('fileId', 'int');
		$this->addType('size', 'int');
	}

	public function getUri(IURLGenerator $urlGenerator) {
		return $urlGenerator->linkToRoute(
			'music.api.track',
			['trackIdOrSlug' => $this->id]
		);
	}

	public function getArtistWithUri(IURLGenerator $urlGenerator) {
		return [
			'id' => $this->artistId,
			'uri' => $urlGenerator->linkToRoute(
				'music.api.artist',
				['artistIdOrSlug' => $this->artistId]
			)
		];
	}

	public function getAlbumWithUri(IURLGenerator $urlGenerator) {
		return [
			'id' => $this->albumId,
			'uri' => $urlGenerator->linkToRoute(
				'music.api.album',
				['albumIdOrSlug' => $this->albumId]
			)
		];
	}

	public function toCollection($l10n) {
		return [
			'title' => $this->getTitle(),
			'number' => $this->getNumber(),
			'disk' => $this->getDisk(),
			'artistName' => $this->getArtist()->getNameString($l10n),
			'artistId' => $this->getArtistId(),
			'files' => [$this->getMimetype() => $this->getFileId()],
			'id' => $this->getId(),
		];
	}

	public function toAPI(IURLGenerator $urlGenerator) {
		return [
			'title' => $this->getTitle(),
			'ordinal' => $this->getDiskAdjustedTrackNumber(),
			'artist' => $this->getArtistWithUri($urlGenerator),
			'album' => $this->getAlbumWithUri($urlGenerator),
			'length' => $this->getLength(),
			'files' => [$this->getMimetype() => $urlGenerator->linkToRoute(
				'music.api.download',
				['fileId' => $this->getFileId()]
			)],
			'bitrate' => $this->getBitrate(),
			'id' => $this->getId(),
			'slug' => $this->getId() . '-' . $this->slugify('title'),
			'uri' => $this->getUri($urlGenerator)
		];
	}

	public function getDiskAdjustedTrackNumber() {
		// On single-disk albums, the track number is given as-is.
		// On multi-disk albums, the disk-number is applied to the track number.
		// In case we have no Album reference, the best we can do is to apply the
		// disk number if it is greater than 1. For disk 1, we don't know if this
		// is a multi-disk album or not.
		$numberOfDisks = ($this->album) ? $this->album->getNumberOfDisks() : null;
		$trackNumber = $this->getNumber();

		if ($this->disk > 1 || $numberOfDisks > 1) {
			$trackNumber = $trackNumber ?: 0;
			$trackNumber += (100 * $this->disk);
		}

		return $trackNumber;
	}

	public function getFileExtension() {
		$parts = \explode('.', $this->getFilename());
		return \end($parts);
	}

	public static function compareArtistAndTitle(Track $a, Track $b) {
		$artistResult = Util::stringCaseCompare(
				$a->getArtist()->getName(), $b->getArtist()->getName());

		return $artistResult ?: Util::stringCaseCompare($a->getTitle(), $b->getTitle());
	}
}
