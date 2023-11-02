<?php

namespace Local;

use Nether\Common;

class CleanupRule
extends Common\Prototype {

	public string
	$Type;

	public string
	$Find;

	public string
	$Format;

	public bool
	$Solo = TRUE;

	public function
	GetFormatTokens():
	Common\Datastore {

		$Output = (
			Common\Text::TemplateFindTokens($this->Format)
			->Remap(Common\Filters\Numbers::IntType(...))
		);

		return $Output;
	}

	public function
	ToArray():
	array {

		return [
			'Type'   => $this->Type,
			'Find'   => $this->Find,
			'Format' => $this->Format,
			'Solo'   => $this->Solo
		];
	}

}
