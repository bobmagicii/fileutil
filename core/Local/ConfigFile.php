<?php

namespace Local;

use Nether\Common;

class ConfigFile
extends Common\Prototype {

	public string
	$Filename;

	#[Common\Meta\PropertyFactory('FromArray', 'Cleanup')]
	public array|Common\Datastore
	$Cleanup = [];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		($this->Cleanup)
		->Remap(fn($D)=> new CleanupRule($D));

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	ToArray():
	array {

		return [
			'Cleanup' => $this->Cleanup->Map(fn($D)=> $D->ToArray())
		];
	}

	public function
	ToJSON():
	string {

		return Common\Text::ReadableJSON($this->ToArray());
	}

	public function
	SetFilename(string $File):
	static {

		$this->Filename = $File;

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromFile(string $File):
	static {

		if(!file_exists($File))
		throw new Common\Error\FileNotFound($File);

		if(!is_readable($File))
		throw new Common\Error\FileUnreadable($File);

		////////

		$Data = file_get_contents($File);

		if($Data === FALSE)
		throw new Common\Error\RequiredDataMissing('Data', 'string<json>');

		$JSON = json_decode($Data, TRUE);

		if(!is_array($JSON))
		throw new Common\Error\RequiredDataMissing('JSON', 'array');

		////////

		$Output = new static($JSON);
		$Output->SetFilename($File);

		return $Output;
	}

}
