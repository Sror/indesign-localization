@echo off
echo %1 %2
if /i %1 == pdf2jpg goto :pdf2jpg
if /i %1 == docx2pdf goto :docx2pdf
goto :eof

:docx2pdf
cd /d "E:\Server Documents\"
IF NOT EXIST "E:\Server Documents\Output\%2.docx"  mkdir "E:\Server Documents\Output\%2.docx"
cmd /c cscript /nologo "C:\Program Files (x86)\Utilites\docx2pdf2.vbs" "E:\Server Documents\Output\%2.docx\%2.docx" /o:"E:\Server Documents\Output\%2.docx\%2.pdf" /verbose
goto :eof
:pdf2jpg
cd /d "E:\Server Documents\"
IF NOT EXIST "E:\Server Documents\Output\%2.docx"  mkdir "E:\Server Documents\Output\%2.docx"
convert -density 150 "E:\Server Documents\Output\%2.docx\%2.pdf" "E:\Server Documents\Output\%2.docx\%2.jpg"
goto :eof

:eof