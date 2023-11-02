@ECHO OFF

CALL php bin\fileutil.php phar
CALL copy build\fileutil.phar \ /y
