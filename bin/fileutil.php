<?php

use Nether\Common;
use Nether\Console;

require(sprintf(
	'%s/vendor/autoload.php',
	dirname(__FILE__, 2)
));

#[Console\Meta\Application('FileUtil', '0.0.1-dev')]
class App
extends Console\Client {

	public Local\ConfigFile
	$Config;

	#[Console\Meta\Command('config')]
	#[Console\Meta\Info('Generate a fresh configuration file.')]
	#[Console\Meta\Arg('ruleset', '[Required] Name of the rulset. Default: fileutil')]
	#[Console\Meta\Arg('dir', '[Optional] Directory to run on. Default is current working directory.')]
	#[Console\Meta\Toggle('--commit', 'Force overwrites and the like.')]
	#[Console\Meta\Error(1, 'Unable to write %s')]
	public function
	HandleConfig():
	int {

		$InputFile = $this->GetInput(1) ?? 'fileutil';
		$InputDir = $this->GetInput(2);
		$OptForce = $this->GetOption('commit');

		$ConfigFilePath = $this->GetConfigFilePathInputBased($InputFile, $InputDir);
		$Fresh = new Local\ConfigFile;

		////////

		$this->PrintLn($this->FormatHeaderLine('FileUtil Config Generate'));

		$this->PrintLn($this->FormatBulletList([
			'File' => $ConfigFilePath
		]));

		if(file_exists($ConfigFilePath) && !$OptForce) {
			$this->PrintLn($this->Format('File Already Exists!', static::FmtError));
			$this->PrintLn($this->Format('Use --commit to force overwrite.', static::FmtAccent), 2 );
			return 0;
		}

		if(!is_writable(dirname($ConfigFilePath)))
		$this->Quit(1, $ConfigFilePath);

		////////

		file_put_contents($ConfigFilePath, $Fresh->ToJSON());

		return 0;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Console\Meta\Command('cleanup')]
	#[Console\Meta\Info('Run the specified cleanup rules on a directory.')]
	#[Console\Meta\Arg('ruleset', '[Required] JSON file that contains the rules to apply.')]
	#[Console\Meta\Arg('dir', '[Optional] Directory to run on. Default is current working directory.')]
	#[Console\Meta\Toggle('--commit', 'Allow it to do what it wants for real.')]
	#[Console\Meta\Error(1, 'Config File Error: %s')]
	public function
	HandleCleanup():
	int {

		$InputFile = $this->GetInput(1) ?? 'fileutil';
		$InputDir = realpath($this->GetInput(2) ?? getcwd());
		$OptCommit = !!$this->GetOption('commit');
		$OptLinePerFile = !!$this->GetOption('line');

		try {
			$this->Config = Local\ConfigFile::FromFile(
				$this->GetConfigFilePathFileBased($InputFile, $InputDir)
			);
		}

		catch(Exception $Err) {
			$this->Quit(1, $Err->GetMessage());
		}

		////////

		$Files = Common\Filesystem\Indexer::DatastoreFromPath($InputDir, FALSE);
		$Files->Remap(fn(string $F)=> basename($F));
		$Todo = new Common\Datastore;

		$File = NULL;
		$Rename = NULL;

		////////

		foreach($Files as $File) {
			$Rename = $this->CustomRenameFunc($File);

			if($Rename && $Rename !== $File)
			$Todo[$File] = $Rename;
		}

		////////

		$this->PrintLn($this->FormatHeading('FileUtil Cleanup'));

		$this->PrintLn($this->FormatBulletList([
			'Config File'        => $this->Config->Filename,
			'Directory To Clean' => $InputDir,
			'Files To Rename'    => $Todo->Count()
		]));

		if($OptLinePerFile)
		$this->PrintLn($this->FormatBulletList($Todo));
		else
		$this->PrintLn($this->FormatTopicList($Todo, static::FmtMuted, static::FmtDefault));

		////////

		if(!$OptCommit) {
			$this->PrintLn($this->Format(
				'Use --commit to run it for real.',
				static::FmtAccent
			));

			return 0;
		}

		////////

		$Todo->Each(
			fn(string $New, string $Old)
			=> rename(
				Common\Filesystem\Util::Prefix($InputDir, $Old),
				Common\Filesystem\Util::Prefix($InputDir, $New)
			)
		);

		return 0;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2023-10-31')]
	#[Common\Meta\Info('Determine a config file path based on the first one it finds on disk.')]
	protected function
	GetConfigFilePathFileBased(string $File, string $TargetDir):
	string {

		// require json extension on the config files.

		if(!str_ends_with($File, '.json'))
		$File .= '.json';

		// if not given a specific path to the file try to find it in
		// the target directory. fall back to the conf directory.

		if(!str_contains($File, DIRECTORY_SEPARATOR)) {
			$File = Common\Filesystem\Util::Pathify($TargetDir, $File);

			if(!file_exists($File))
			$File = Common\Filesystem\Util::Pathify(
				dirname($this->File, 2), basename($File)
			);
		}

		return $File;
	}

	#[Common\Meta\Date('2023-11-01')]
	#[Common\Meta\Info('Determine a possible valid config file path based on inputs.')]
	protected function
	GetConfigFilePathInputBased(string $File, ?string $TargetDir):
	?string {

		// require json extension on the config files.

		if(!str_ends_with($File, '.json'))
		$File .= '.json';

		// if no directory was input then try to place the config
		// file next to this file.

		if(!$TargetDir)
		$TargetDir = dirname($this->File, 2);

		return Common\Filesystem\Util::Pathify($TargetDir, $File);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	CustomRenameFunc(string $File):
	?string {

		// @todo 2023-10-30 pull these patterns from a json file.

		$Patterns = $this->Config->Cleanup->Map(fn($D)=> $D);
		$Output = $File;
		$Filter = NULL;
		$Match = NULL;
		$Set = NULL;

		////////

		foreach($Patterns as $Filter) {
			$Match = NULL;
			$Set = $Filter->GetFormatTokens();

			////////

			if($Filter->Type === 'regfmt')
			if(preg_match($Filter->Find, $File, $Match)) {
				$Output = $Filter->Format;

				foreach($Set as $Tokey) {
					$Token = Common\Text::TemplateMakeToken($Tokey);

					$Output = str_replace(
						$Token,
						$Match[$Tokey],
						$Output
					);
				}

				if($Filter->Solo)
				return $Output;
			}

			////////

			continue;
		}

		return NULL;
	}

	protected function
	CustomCollisionFunc(string $File):
	string {

		$Output = $File;

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	// OVERLOADS Console\Client PHAR ///////////////////////////////

	protected function
	GetPharFiles():
	Common\Datastore {

		$Output = parent::GetPharFiles();

		$Output->MergeRight([
			Common\Filesystem\Util::Pathify('core', 'Local')
		]);

		return $Output;
	}

	protected function
	GetPharFileFilters():
	Common\Datastore {

		$Output = Common\Datastore::FromArray([
			fn($D)=> !preg_match('#\W\.(?:git|vscode)#', $D),
			fn($D)=> !preg_match('#squizlabs\Wphp_codesniffer#', $D),
			fn($D)=> !preg_match('#monolog\Wmonolog#', $D),
			fn($D)=> !preg_match('#netherphp\Wstandards#', $D),
			fn($D)=> !preg_match('#\Wtests\W#', $D)
		]);

		return $Output;
	}

};

exit((new App)->Run());

//exit(App::Realboot([ ]));
