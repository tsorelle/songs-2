@echo off
rem Run from project root
echo Build...
echo If errors see \sass\notes.txt
d:
cd  sass\default
del ..\output\styles.*
call sass .\styles.scss:..\output\styles.css^
 --load-path=..\assets^
 --style=compressed

copy ..\output\styles.* ..\..\web.root\application\themes\default\ /-Y
cd ../..
