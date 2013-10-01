set output = wscript.stdout
Dim arguments: Set arguments = WScript.Arguments
If arguments.Count=0 Or arguments.Named.Exists("session")=false Then
	output.WriteLine("Please set session")
Else
	Session = arguments.Named.Item("session")
	output.WriteLine("Session:" & Session)
	Const ForReading = 1
	path = "C:\Program Files\DocxService\" & Session & ".txt"
	Dim arrFileLines()
	looping = true
	While looping
		'looping = false
		Set objFSO = CreateObject("Scripting.FileSystemObject")
		If (objFSO.FileExists(path)) Then
			Set objShell = WScript.CreateObject("WScript.Shell")
			
			Set objFile = objFSO.OpenTextFile(path, ForReading)
			Do Until objFile.AtEndOfStream
				''Create PDF
				docxfile = objFile.ReadLine
				output.WriteLine(docxfile)
				
				'cmdline = """C:\Program Files (x86)\Utilites\pagl_convert.bat"" docx2pdf " & docxfile
				cmdline = """C:\Program Files (x86)\Utilites\pagl_convert.bat"" " & docxfile
				'output.WriteLine(cmdline)
				'objShell.Run(cmdline)
				Set oExec = objShell.Exec(cmdline)
				Do While oExec.Status <> 1
					WScript.Sleep 100
				Loop
				output.WriteLine("- Done")
				
				''Create JPG
				'cmdline = """C:\Program Files (x86)\Utilites\pagl_convert.bat"" pdf2jpg " & docxfile
				'Set oExec = objShell.Exec(cmdline)
				'Do While oExec.Status <> 1
				'	WScript.Sleep 100
				'Loop
				
			Loop
			objFile.Close
			objFSO.DeleteFile path
		Else
			'waiting
			WScript.Sleep 1000
		End If
	Wend
End if