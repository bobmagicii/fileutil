@ECHO OFF

SET ScriptPath=%~dp0
SET ScriptPath=%ScriptPath:~0,-1%

SET ScriptPHP=%~nx0
SET ScriptPHP=%ScriptPHP:bat=php%

CALL php %ScriptPath%\%ScriptPHP% %*
