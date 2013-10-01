'
' DOC2PDF2.VBS Microsoft Scripting Host Script (Requires Version 5.6 or newer)
' --------------------------------------------------------------------------------
' Author: Carsten Cumbrowski 
' Created: December 2008 and February 2009
' 
' Based on a script by  Michael Suodenjoki
' This script can create a PDF file from a Word document provided you're using
' Word 2007 and have the 'Office Add-in: Save As PDF' installed.
' It can also save the documents in other formats that are supported by MS Word 2007
' The script allows batch processing of multiple files (e.g. a directory) and also the 
' sending via email to Google Docs (output format DOC, Office 97 required) 
'
' Script must be called with CSCRIPT instead of WSCRIPT
' It's not the end of the world, if you don't, because the script will relaunch itself 
' using CSCRIPT, if you execute it with WSCRIPT anyway. However that might cause other
' issues, depending how you are using this script
' For example the redirection of the output into a text file won't work, because 
' the script opens a new DOS session

ForceCscript true
' Constants

Const iGoogleDocsSizeLimit = 5120000
'Const iGoogleDocsReqFormat = 0

Const WdDoNotSaveChanges = 0
Const EmailSendMethod = 2   '1 = MAPI (Outlook), 2 = CDO

' see WdSaveFormat enumeration constants: 
' http://msdn2.microsoft.com/en-us/library/bb238158.aspx
Const wdFormatPDF = 17  ' PDF format. 
Const wdFormatXPS = 18  ' XPS format. 
Const wdFormatDocument97=0  'Word97 Format
Const wdFormatDocumentDefault = 16 'Word Default (.DOCX in Word2007)
Const wdFormatWebArchive = 9 'Web Archive
Const wdFormatTemplate = 1 'Word 97 Template
Const wdFormatRTF = 6 'RTF
Const wdFormatHTML = 8 'Std HTML
Const wdFormatFilteredHTML = 10 'Filtered HTML
Const wdFormatText = 2 'Windows Text
Const wdFormatTextLineBreaks = 3 'Windows Text with line-breaks preserved
Const wdFormatEncodedText = 7 'Encoded Text (Unicode)
Const wdFormatUnicodeText = 7
Const wdFormatDOSTextLineBreaks = 5 'DOS Text w Line-Breaks
Const wdFormatDOSText = 4 'DOS
Const wdFormatXML = 11 'XML
Const wdFormatXMLDocument = 12 'XML DOc   .docx
Const wdFormatXMLDocumentMacroEnabled = 13 'XML Macros Enabled  .docm
Const wdFormatXMLTemplate = 14 'XML Template .dotx
Const wdFormatXMLTemplateMacroEnabled = 15 'XML Template Macros Enabled  .dotm

Const TEXTMSG = 0
Const HTMLMSG = 1

' Global variables
Dim ofso, iDays, bShowDebug,  iwdFormat, sOutExt, sFolder, bVerbose,output,  ol, ns
Dim owdo   'Word.Application
Dim owdocs 'Word.Documents

'Global Variables with Initial Values
Dim arguments: Set arguments = WScript.Arguments
Dim sOutFormat: sOutFormat = "PDF"
Dim bDateFilter: bDateFilter = false
Dim sDateFilterType: sDateFilterType = ""
Dim sDateFrom: sDateFrom = ""
Dim sDateTo: sDateTo = ""
Dim sGoogleEmail: sGoogleEmail = ""



'-------------------------------------------------------------------------------------------------
Function DetermineOutFormat(sOut)
  Dim iOut
  
  Select Case ucase(sOUt)
    Case ""
          iOut = wdFormatPDF 
          sOutExt = "pdf"
    Case "PDF"
          iOut = wdFormatPDF 
    Case "XPS"
          iOut = wdFormatXPS 
    Case "DOC"
          iOut = wdFormatDocument97
    Case "DOT"
          iOUt = wdFormatTemplate 
    Case "HTML"
          iOut = wdFormatHTML 
    Case "HTM"
          iOut = wdFormatFilteredHTML 
    Case "DOCX"
          iOut = wdFormatXMLDocument 
    Case "DOCM"
          iOut = wdFormatXMLDocumentMacroEnabled 
    Case "DOTX"
          iOut = wdFormatXMLTemplate 
    Case "DOTM"
          iOUt = wdFormatXMLTemplateMacroEnabled 
    Case "TXT"
          iOut = wdFormatTextLineBreaks 
    Case "XML"
          iOUt = wdFormatXML 
    Case "ASC"
          iOUt = wdFormatDOSTextLineBreaks 
    Case "DOS"
          iOUt = wdFormatDOSText 
          sOutExt = "txt"
    Case "RTF"
          iOut =     wdFormatRTF     
    Case "WEB"
          iOUt = wdFormatWebArchive 
    Case else
         iOut = -1
  End Select
  DetermineOutFormat = iOut

End Function

'-------------------------------------------------------------------------------------------------
' ECHOUSAGE
' Outputs the usage information.
'
Function EchoUsage()
  If arguments.Count=0 Or arguments.Named.Exists("help") Or arguments.Named.Exists("h") or iwdFormat=-1 Then
    output.WriteLine "Save Word 2007 Compatible Document(s) in a different Format and more"
    output.WriteLine ""
    output.WriteLine "Usage: cscript /nologo doc2pdf.vbs [<input-file>] [/dir:<input-dir>] "
    output.WriteLine "       [/date:created/modified] [/datefrom:mm-dd-yyyyy] [/dateto:mm-dd-yyyy] [/days:nn] "
    output.WriteLine "       [/f:<output-format>] [/o:<output-file>] [/googledocs:<email>]"
    output.WriteLine  ""
    output.WriteLine  "Available Options:"
    output.WriteLine  "=================="
    output.WriteLine  " /help   - Specifies that this usage/help information should be displayed."
    output.WriteLine  " /verbose  - Specifies that detailed information about the processing should be displayed."
    output.WriteLine  ""
    output.WriteLine  "Parameters (optional):"
    output.WriteLine  "======================"
    output.WriteLine  " /dir:<input-dir>"
    output.WriteLine  "    Directory Batch Processing. Use <input-file> argument as "
    output.WriteLine  "    filter option (e.g. *.DOCX or *.* for all)"
    output.WriteLine  ""
    output.WriteLine  "Date Filter Options"
    output.WriteLine  "--------------------"
    output.WriteLine "/date:created/modified - Date filter option"
    output.WriteLine "/datefrom:mm-dd-yyy - From Date for date-created or date-modfied filter"
    output.WriteLine "/datetto:mm-dd-yyy - To Date for date-created or date-modfied filter"
    output.WriteLine " ...or .."
    output.WriteLine "/days:nn - created/changed within the last nn days. "
    output.WriteLine ""
    output.WriteLine "Output Options Parameters"
    output.WriteLine "-------------------------"
    output.WriteLine  " /f:<format> Specifies the output format values:"     
    output.WriteLine  "    DOCX, DOCM, DOTX, DOTM, XML, PDF (default), XPS, TXT, DOS, "
    output.WriteLine  "    ASC, HTML, HTM, RTS, DOC, WEB" 
    output.WriteLine  " /o:<file> Optionally specification of output file."
    output.WriteLine ""
    output.WriteLine "Send to Google Docs via Email Option"
    output.WriteLine "------------------------------------"
    output.WriteLine "Note: Google Docs only supports the import of documents in DOC file format (Office 97)."
    output.WriteLine "PDF files are not supported. The file size is also limited to 500KB per document."
    output.WriteLine ""
    output.WriteLine "/googledocs:<email> - your personal email to upload documents, "
    output.WriteLine "                      which looks like: longstring@prod.writely.com "
    output.WriteLine ""      
    output.WriteLine  "Examples:"
    output.WriteLine  "========="
    output.WriteLine "1. Convert all Word 2007 documents (.docx) that were modified on or "
    output.WriteLine "   since December 31, 2008 and are located in 'C:\DOCS' to PDF and show "
    output.WriteLine "   detailed information about the processing."
    output.WriteLine ""
    output.WriteLine "cscript /nologo docx2pdf2.vbs *.docx /f:PDF /dir:C:\DOCS "
    output.WriteLine "        /date:modified /datefrom:12-31-2008 /verbose"
    output.WriteLine ""
    output.WriteLine "2. Convert all Word 2007 documents (.docx) that were created "
    output.WriteLine "   in the last 7 days and are located in 'C:\MY DOCUMENTS' to Word 97 (.DOC) and "
    output.WriteLine "   send them to Google Docs. "
    output.WriteLine ""
    output.WriteLine "cscript /nologo docx2pdf2.vbs *.docx /f:DOC /dir:""C:\MY DOCUMENTS"" "
    output.WriteLine "        /date:created /days:7 /googledocs:mysecretemail@prod.writely.com"
    output.WriteLine ""
    output.WriteLine "3. Convert a single Word 2007 Document 'C:\DOCS\mydocument.docx' to PDF and "
    output.WriteLine "   save it as 'C:\MY DOCUMENTS\mynewdocument.pdf'"
    output.WriteLine ""
    output.WriteLine "cscript /nologo docx2pdf2.vbs ""C:\DOCS\mydocument.docx"" "
    output.WriteLine "        /o:""C:\MY DOCUMENTS\mynewdocument.pdf"""
    output.WriteLine ""
    output.WriteLine "4. Convert 'C:\DOCS\mydocument.docx' to HTML and save it in the same folder"
    output.WriteLine ""
    output.WriteLine "cscript /nologo docx2pdf2.vbs ""C:\DOCS\mydocument.docx"" /f:HTML"
    output.WriteLine ""                         
  End If 
End Function

'-------------------------------------------------------------------------------------------------
' CHECKARGS
'' Makes some preliminary checks of the arguments.
' Quits the application is any problem is found.
'
Function CheckArgs()
  ' Check that <doc-file> is specified
  If arguments.Unnamed.Count <> 1 Then
    output.WriteLine "Error: Obligatory <doc-file> parameter missing!"
    WScript.Quit 1
  End If

  if arguments.Named.Exists("f") then
      sOutFormat = arguments.Named.Item("f")
  end if

  if arguments.Named.Exists("googledocs") then
    sGoogleEmail =  arguments.Named.Item("googledocs")
  end if    
  if sGoogleEmail <> "" then
     sOutFormat = "DOC"
  end if

  sOutExt = sOutFormat
  iwdFormat = DetermineOutFormat(sOutFormat)
  
  if arguments.Named.Exists("date") then
  
      if arguments.Named.Exists("days") then
         if isNumeric(arguments.Named.Item("days")) then
            iDays = int(abs(arguments.Named.Item("days")))
            sDateFrom = formatdatetime(dateadd("d",iDays*-1,now),vbshortdate)
            sDateTo = formatdatetime(now,vbshortdate)
         end if
       else
          if arguments.Named.Exists("datefrom") then
            if isDate(arguments.Named.Item("datefrom")) then
              sDateFrom = formatdatetime(arguments.Named.Item("datefrom"),vbshortdate)
            end if
          end if
          if arguments.Named.Exists("dateto") then
             if isDate(arguments.Named.Item("dateto")) then
                sDateTo = formatdatetime(arguments.Named.Item("dateto"),vbshortdate)
             end if
          else
             sDateTo = formatdatetime(now,vbshortdate)
          end if
       end if
           
       if arguments.Named.Item("date") = "created" then
          sDateFilterType = "c"
       end if
       if arguments.Named.Item("date") = "modified" then
          sDateFilterType = "m"
       end if
      
       if sDateFilterType <> "" and sDateFrom <> "" and sDateTo <> "" then
          bDateFilter = true
          if bVerbose = true then
            output.WriteLine "Date Filter on! From Date: " & sDateFrom & ", To Date: " & _
                              sDateTo & ", Filter: " & sDateFilterType
          end if
       end if
  
  end if

End Function

'-------------------------------------------------------------------------------------------------
' DOC2PDF
' Converts a Word document to PDF using Word 2007.
' Input:
' sDocFile - Full path to Word document.
' sPDFFile - Optional full path to output file.
'
' If not specified the output PDF file
' will be the same as the sDocFile except
' file extension will be .pdf.
'
Function DOC2PDF( sDocFile, sPDFFile )

  Dim owdoc ' As Word.Document
  Dim sPrevPrinter ' As String

  if sFolder = "" then
    sDocFile = ofso.GetAbsolutePathName(sDocFile)
    sTmpFolder = ofso.GetParentFolderName(sDocFile)
  else
    sDocFile = sDocFile
    sTmpFolder = sFolder
  end if

  If Len(sPDFFile)=0 Then
    sPDFFile = ofso.GetBaseName(sDocFile) + "." + lcase(sOutExt)
  End If

  If Len(ofso.GetParentFolderName(sPDFFile))=0 Then
    sPDFFile = sTmpFolder + "\" + sPDFFile
  End If

  ' Enable this line if you want to disable autoexecute macros
  ' owdo.WordBasic.DisableAutoMacros
  if bVerbose = true then
    output.WriteLine "Converting: " & sDocFile 
  end if                
  ' Open the Word document
  Set owdoc = owdocs.Open(sDocFile)

  ' Let Word document save as PDF
  ' - for documentation of SaveAs() method,
  '   see http://msdn2.microsoft.com/en-us/library/bb221597.aspx 
  owdoc.SaveAs sPDFFile, iwdFormat 

  owdoc.Close WdDoNotSaveChanges

  if bVerbose = true then
     output.WriteLine "Saved As: " & sPDFFile & "(Format: " & iwdFormat & ")"
  end if                

  if sGoogleEmail <> "" then
     SendEmailToGoogleDocs sGoogleEmail, sPDFFile
  end if

End Function

'-------------------------------------------------------------------------------------------------
' Returns an array with the file names that match Path.
' The Path string may contain the wildcard characters "*"
' and "?" in the file name component. The same rules apply
' as with the MSDOS DIR command.
' If Path is a directory, the contents of this directory is listed.
' If Path is empty, the current directory is listed.
' Author: Christian d'Heureuse (www.source-code.biz)

Public  Function ListDir (ByVal Path)
   Dim fso: Set fso = CreateObject("Scripting.FileSystemObject")
   If Path = "" then Path = "*.*"
   Dim Parent, Filter
   if fso.FolderExists(Path) then      ' Path is a directory
      Parent = Path
      Filter = "*"
   Else
      Parent = fso.GetParentFolderName(Path)
      If Parent = "" Then If Right(Path,1) = ":" Then Parent = Path: Else Parent = "."
      Filter = fso.GetFileName(Path)
      If Filter = "" Then Filter = "*"
   End If
   ReDim a(10)
   Dim n: n = 0
   Dim Folder: Set Folder = fso.GetFolder(Parent)
   Dim Files: Set Files = Folder.Files
   Dim File
   For Each File In Files

      bDateFiltered = false
      if bDateFilter = true then
        if sDateFilterType = "c" then
          dDate = File.DateCreated 
        end if            
        if sDateFilterType = "m" then
          dDate = File.DateLastModified
        end if
        if datediff("d",dDate,sDateFrom) >= 0  or datediff("d",dDate,sDateTo) < 0 then
          bDateFiltered = true
          if bVerbose = true then
             output.WriteLine File.Name & " skipped (Date Filter), (dc: " & _
                             File.DateCreated & ", dm: " & File.DateLastModified & ")" 
          end if
        end if      
      end if
      if  bDateFiltered = false then
          If CompareFileName(File.Name,Filter) Then
              If n > UBound(a) Then ReDim Preserve a(n*2)
              a(n) = File.Path
              n = n + 1
          else
            if bVerbose = true then
              output.WriteLine File.Name & " skipped (File Path Filter Argument)" 
            end if                    
          End If
      end if
   Next
   ReDim Preserve a(n-1)
   ListDir = a
End Function
     
'-------------------------------------------------------------------------------------------------
Private Function CompareFileName (ByVal Name, ByVal Filter) ' (recursive)
   CompareFileName = False
   Dim np, fp: np = 1: fp = 1
   Do
      If fp > Len(Filter) Then CompareFileName = np > len(name): Exit Function
      If Mid(Filter,fp) = ".*" Then    ' special case: ".*" at end of filter
         If np > Len(Name) Then CompareFileName = True: Exit Function
      End If
      If Mid(Filter,fp) = "." Then     ' special case: "." at end of filter
         CompareFileName = np > Len(Name): Exit Function
      End If
      Dim fc: fc = Mid(Filter,fp,1): fp = fp + 1
      Select Case fc
         Case "*"
            CompareFileName = CompareFileName2(name,np,filter,fp)
            Exit Function
         Case "?"
            If np <= Len(Name) And Mid(Name,np,1) <> "." Then np = np + 1
         Case Else
            If np > Len(Name) Then Exit Function
            Dim nc: nc = Mid(Name,np,1): np = np + 1
            If Strcomp(fc,nc,vbTextCompare)<>0 Then Exit Function
      End Select
   Loop
End Function
     
'-------------------------------------------------------------------------------------------------
Private Function CompareFileName2 (ByVal Name, ByVal np0, ByVal Filter, ByVal fp0)
   Dim fp: fp = fp0
   Dim fc2
   Do                                  ' skip over "*" and "?" characters in filter
      If fp > Len(Filter) Then CompareFileName2 = True: Exit Function
      fc2 = Mid(Filter,fp,1): fp = fp + 1
      If fc2 <> "*" And fc2 <> "?" Then Exit Do
      Loop
   If fc2 = "." Then
      If Mid(Filter,fp) = "*" Then     ' special case: ".*" at end of filter
         CompareFileName2 = True: Exit Function
         End If
      If fp > Len(Filter) Then         ' special case: "." at end of filter
         CompareFileName2 = InStr(np0,Name,".") = 0: Exit Function
         End If
      End If
   Dim np
   For np = np0 To Len(Name)
      Dim nc: nc = Mid(Name,np,1)
      If StrComp(fc2,nc,vbTextCompare)=0 Then
         If CompareFileName(Mid(Name,np+1),Mid(Filter,fp)) Then
            CompareFileName2 = True: Exit Function
            End If
         End If
      Next
   CompareFileName2 = False
   End Function
     
'-------------------------------------------------------------------------------------------------
Sub SendEmailToGoogleDocs(sToEmail, sFileAttachment)
  Dim ToAddress
  Dim MessageSubject
  Dim MessageBody
  Dim MessageAttachment
  Dim newMail
  
  Set oCheckFile = oFso.GetFile(sFileAttachment)
  if oCheckFile.Size > iGoogleDocsSizeLimit then
  'File is too large
    output.WriteLine sFileAttachment & " could not be sent to Google Docs. " & _
                     "It is too large (limit 500KB). File Size: " & oCheckFile.Size/1024 & "KB" 
  else
  
      ToAddress = sToEmail
      MessageSubject = oFso.GetFileName(sFileAttachment)
      MessageBody = ""
          
      Select Case EmailSendMethod 
          
          Case 1
            'Send EMail using MAPI
                
            Set ol = WScript.CreateObject("Outlook.Application")
            Set ns = ol.getNamespace("MAPI")
            ns.logon "","",true,false
             
            Set newMail = ol.CreateItem(olMailItem)
            newMail.Subject = MessageSubject
            newMail.Body = MessageBody &vbCrLf
            
            MessageAttachment = sFileAttachment
            newMail.Attachments.Add(MessageAttachment).Displayname = oFso.GetFileName(sFileAttachment)
            
            ' validate the recipient, just in case…
            Set myRecipient = ns.CreateRecipient(ToAddress)
            myRecipient.Resolve
            
            If Not myRecipient.Resolved Then
                output.WriteLine  sToEmail & " is an Unknown recipient," & _
                                  " cannot send email with " & sFileAttachment
            Else
               newMail.Recipients.Add(myRecipient)
               newMail.Send
               if bVerbose = true then
                 output.WriteLine "Email for Google Docs for " & sFileAttachment & _
                                  " was created in our Outlook 'Outbox' folder." 
               end if                     
            End If
        
            Set ol = Nothing
    
        Case 2
          'Send EMail Using CDO
            msgType  = 0 
        
            Set newMail = Wscript.CreateObject("CDO.Message")
            newMail.To = sToEmail
            newMail.From = sToEmail
            newMail.Subject = MessageSubject
            if msgType = TEXTMSG then
              newMail.TextBody = MessageBody &vbCrLf
            else
              newMail.HTMLBody = MessageBody &vbCrLf
            end if
            newMail.AddAttachment  sFileAttachment
            if bVerbose = true then
              output.WriteLine "Email for Google Docs for " & sFileAttachment & " was sent via CDO" 
            end if                     
  
  '         For the configuration of a custom SMTP Server           
  '         newMail.Configuration.Fields.Item _
  '           ("http://schemas.microsoft.com/cdo/configuration/sendusing") = 2
  '         newMail.Configuration.Fields.Item _
  '           ("http://schemas.microsoft.com/cdo/configuration/smtpserver") = "SERVERIP or HOSTNAME"
  '         newMail.Configuration.Fields.Item _
  '           ("http://schemas.microsoft.com/cdo/configuration/smtpserverport") = 25
  '         newMail.Configuration.Fields.Update
  
            newMail.Send
            set newMail = Nothing
  
      End Select
          
  end if

End Sub     

'-------------------------------------------------------------------------------------------------
Sub ForceCscript(bForceRelance) 
 'Force to reload the program in non interactive mode 
  If (right(Ucase(WScript.FullName),11)="WSCRIPT.EXE") and bForceRelance Then 
    Dim WshShell,args,objArgs,argname, I , sFullCall
    Set WshShell = CreateObject("WScript.Shell") 
    args="" 
    If Wscript.Arguments.Count > 0 Then 
      Set objArgs = Wscript.arguments.Unnamed 
      For I = 0 to objArgs.Count - 1
        if instr(objArgs(I)," ") > 0 then
          args = args & " """ & objArgs(I) & """"
        else 
          args = args & " " & objArgs(I)
        end if         
      Next 
      For each argname in Wscript.arguments.Named 
        if instr(Wscript.arguments.Named.Item(argname)," ") > 0 then
          args = args & " /" & argname & ":""" & Wscript.arguments.Named.Item(argname) & """"
        else
          if Wscript.arguments.Named.Item(argname) = "" then
            args = args & " /" & argname 
          else
            args = args & " /" & argname & ":" & Wscript.arguments.Named.Item(argname)
          end if        
        end if
      Next
   End If
   sFullCall =  WshShell.ExpandEnvironmentStrings("%COMSPEC%") & " /C cscript.exe """ & _
                Wscript.ScriptFullName & """" & args
  
   WshShell.Run  sFullCall,1,False 
   Set WshShell = Nothing 
   WScript.Quit 
 End If 
End Sub 

'=======================================================================
' *** MAIN **************************************
'=======================================================================

set output = wscript.stdout
bVerbose = arguments.Named.Exists("verbose") Or arguments.Named.Exists("v")

Call EchoUsage()
Call CheckArgs()

Set ofso = CreateObject("Scripting.FileSystemObject")
Set owdo = CreateObject("Word.Application")
Set owdocs = owdo.Documents

sFolder= ""

if not arguments.Named.Exists("dir") then
   'Single Document Conversion
   Call DOC2PDF( arguments.Unnamed.Item(0), arguments.Named.Item("o") )
else
   'Batch Conversion
   sFolder = arguments.Named.Item("dir")
   if right(sFolder,1) = "\" then
     sFolder = left(sFolder, Len(sFolder)-1)
   end if 

   if Not oFso.FolderExists(sFolder)  then
      output.WriteLine  "Error: Input Folder for Batch Processing does not exist!"
   else
'      WScript.Echo sFolder & "\" & arguments.Unnamed.Item(0)
       aFiles = ListDir(sFolder & "\" & arguments.Unnamed.Item(0))
       If UBound(aFiles) = -1 then
          output.WriteLine  "No files found."
       end if
       For Each FileName In aFiles 
          Call DOC2PDF(FileName,"")  
       Next
   end if

end if

owdo.Quit WdDoNotSaveChanges

Set owdo = Nothing
Set arguments = Nothing
Set oFso = Nothing
        