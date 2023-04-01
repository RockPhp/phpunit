phpunit with eclipse pdt integration for php 5.1.6

to trick eclipse, need to follow some steps:

(worked with Version: 2023-03 (4.27.0) Build id: 20230309-1520)

- copy some valid php binary to some path.
- inside eclipse window/preferences Installed Phps, add this php copy with some nice name (ex php5.1), apply/close.
- replace the valid php binary with php5.1-eclipse-pdt-docker.sh.
- open SomeClassTest.php, run as PHPUnit Test, will fail
- edit the run SomeClassTest.php, change use Global PHP Unit phar, point to the empty phpunit.phar file
- edit the run SomeClassTest.php, change PHP Script, alternate php, point to installed php (ex php5.1)
- apply/run
