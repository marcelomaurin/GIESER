; Script generated by the Inno Setup Script Wizard.
; SEE THE DOCUMENTATION FOR DETAILS ON CREATING INNO SETUP SCRIPT FILES!

#define MyAppName "srvRAD"
#define MyAppVersion "0.5"
#define MyAppPublisher "MAURINSOFT"
#define MyAppURL "http://maurinsoft.com.br"
#define MyAppExeName "srvrad.exe"

[Setup]
; NOTE: The value of AppId uniquely identifies this application.
; Do not use the same AppId value in installers for other applications.
; (To generate a new GUID, click Tools | Generate GUID inside the IDE.)
AppId={{25E22926-C829-4F42-B173-8912FF91F4D2}
AppName={#MyAppName}
AppVersion={#MyAppVersion}
;AppVerName={#MyAppName} {#MyAppVersion}
AppPublisher={#MyAppPublisher}
AppPublisherURL={#MyAppURL}
AppSupportURL={#MyAppURL}
AppUpdatesURL={#MyAppURL}
DefaultDirName={pf}\{#MyAppName}
DisableProgramGroupPage=yes
OutputBaseFilename=srvrad_setup_05
Compression=lzma
SolidCompression=yes

 

[Languages]
Name: "brazilianportuguese"; MessagesFile: "compiler:Languages\BrazilianPortuguese.isl"

[Tasks]
Name: "desktopicon"; Description: "{cm:CreateDesktopIcon}"; GroupDescription: "{cm:AdditionalIcons}"; Flags: unchecked


[Types]
Name: "srvrad"; Description: {cm:T_srvTemp}

[Components]
Name: "srvrad"; Description: "Arquivos do srvrad"; Types: srvrad;


[CustomMessages]
T_srvTemp=srvrad
TD_srvTemp=Install demo to srvrad

; [Setup], [Files] etc sections go here
[Code]
//#define MSIDT_CustomType "Whatever"
//#include "DescriptiveTypes.isi"
//procedure InitializeWizard();
//begin
// InitializeDescriptiveTypes();
//end;



[Files]
Source: "D:\projetos\maurinsoft\GIESER\software\src\srvrad.exe"; DestDir: "{app}"; Flags: ignoreversion


[Icons]
Name: "{commonprograms}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"
Name: "{commondesktop}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"; Tasks: desktopicon

[Run]
;Filename: "{app}\ELGIN Printer Driver_v-1.6.6.exe"; Description: "{cm:LaunchProgram,{#StringChange(MyAppName, '&', '&&')}}"; Components: Elgin;
;Filename: "{app}\POS Printer Driver Setup .exe"; Description: "{cm:LaunchProgram,{#StringChange(MyAppName, '&', '&&')}}"; Components: Pos58; 
;Filename: "{app}\POS Printer Driver Setup .exe"; Description: "{cm:LaunchProgram,{#StringChange(MyAppName, '&', '&&')}}"; Components: Pos5811; 
Filename: "{app}\{#MyAppExeName}"; Description: "{cm:LaunchProgram,{#StringChange(MyAppName, '&', '&&')}}"; Flags: nowait postinstall skipifsilent
