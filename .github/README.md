# FileUtil

A collection of file system utilities.



# Install (as a system utility)

## Pre-Built Phar

0) (There are no tagged releases yet)
1) Download a PHAR from the latest releases tab here on Github.
2) Put it somewhere in PATH.

## Manual Pharberizing

1) Build the PHAR

   ```shell
   $ composer require bobmagicii/fileutil
   ```

   ```shell
   $ fileutil phar
   ```

   ```shell
   $ php bin\fileutil.php phar
   ```

2) Put the `build/fileutil.phar` somewhere in PATH.



# Install (as a package)

```shell
$ composer require bobmagicii/fileutil
```



# Usage

*Windows Nice Mode*
```shell
C:\Users\bob\Videos> fileutil cleanup strip-sitenames
```

*Windows Lazy Mode*
```shell
C:\Users\bob\Videos> php C:\Local\Tools\fileutil.phar cleanup strip-sitenames
```

In the above situation `fileutil` is a `bat` file sitting next to the `phar` file in the directory `C:\Local\Tools` which is in my shell `PATH`. That is what makes the magic invokation work. Otherwise you will likely have to do something like the second choice.

* `C:\Local\Tools\fileutil.bat`
* `C:\Local\Tools\fileutil.phar`

It will search the directory that it is working on as well as a directory called `fileutil` sitting next to the Phar.

* `C:\Users\bob\Videos\strip-sitenames.json`
* `C:\Local\Tools\fileutil\strip-sitenames.json`

Optionally a directory may be specified after the ruleset name otherwise it will work upon the current working directory. The default is to do a dry run to inspect what it wants to do. Add the `--commit` option to make it go brrrrrt.



# JSON Rules

The following JSON placed in a file called `toggle.json` will cause files named `omg1.txt` to `omg9.txt` to be renamed with a prefix of `bbq` and running it again will send them back.

These `CleanupRule` are Regular Expression formatting rules where if the file name matches the `Find` expression it will be renamed to fit the `Format` pattern. Groups from the regex can be used with the tokens `{%1%}` where the number is the group number.

```json
{
	"Cleanup": [
		{
			"Type": "regfmt",
			"Find": "#^omg(\\d)\\.(txt)#",
			"Format": "bbq{%1%}.{%2%}"
		},
		{
			"Type": "regfmt",
			"Find": "#^bbq(\\d)\\.(txt)#",
			"Format": "omg{%1%}.{%2%}"
		}
	]
}
```

If you have the source code repo then this is one of the tests that can be ran. Watch the files in the `tests\set1` folder while running this a few times to see them flip flop:

```shell
$ fileutil cleanup toggle tests\set1 --commit
```
