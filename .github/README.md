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

Rulesets are JSON files and they are searched for in the current directory and if one is not found then it is looked for in the global `conf` dir.

The default is to do a dry run so you can see what it wants to do. When you are ready to go for real add the `--commit` option.

Run the `video.json` cleanup on the current working directory.

```shell
$ fileutil cleanup video
```

Run the `video.json` cleanup on a specific video folder. It is always best to quote your potentially crazy things.

```shell
$ fileutil cleanup video "C:\Users\bob\Downloads\Video"
```



# JSON Rules

The following JSON placed in a file called `toggle.json` placed either beside the Phar or inside the directory you wish to work on will cause files named `omg1.txt` to `omg9.txt` to be renamed with a prefix of `bbq` and running it again will send them back.

```json
{
	"Rules": [
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
$ php bin\fileutil.php cleanup toggle tests\set1 --commit
```

```shell
$ fileutil cleanup toggle tests\set1 --commit
```
