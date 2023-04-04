unit main;

{$mode objfpc}{$H+}

interface

uses
  Classes, SysUtils, Forms, Controls, Graphics, Dialogs, StdCtrls, Buttons,
  ExtCtrls, Menus, PopupNotifier, LazSerial, FileUtil, LazFileUtils, LazSynaSer,
  synaser, IdHTTPServer, lNetComponents, LedNumber, setmain, registro, peso,
  setup, lNet, log, IdCustomHTTPServer,  IdCompressionIntercept,
  IdSSLOpenSSL, IdSchedulerOfThreadDefault,IdContext, lHTTP, fphttpclient, httpprotocol;

Const
    Version : string =  '0.03';


type

  { Tfrmmain }


  Tfrmmain = class(TForm)
    btDesconectar1: TButton;
    Button1: TButton;
    btConectar: TButton;
    btSetup: TButton;
    IdSchedulerOfThreadDefault1: TIdSchedulerOfThreadDefault;
    IdServerCompressionIntercept1: TIdServerCompressionIntercept;
    lbVersao: TLabel;
    lbstatus: TLabel;
    LazSerial1: TLazSerial;
    MenuItem1: TMenuItem;
    MenuItem2: TMenuItem;
    MenuItem3: TMenuItem;
    btlog: TMenuItem;
    popTray: TPopupMenu;
    PopupNotifier1: TPopupNotifier;
    Timer1: TTimer;
    btsair: TToggleBox;
    TrayIcon1: TTrayIcon;
    procedure btConectarClick(Sender: TObject);
    procedure btDesconectar1Click(Sender: TObject);
    procedure btlogClick(Sender: TObject);
    procedure btsairChange(Sender: TObject);
    procedure btSetupClick(Sender: TObject);
    procedure btTestaClick(Sender: TObject);
    procedure Button1Click(Sender: TObject);
    procedure FormCloseQuery(Sender: TObject; var CanClose: boolean);
    procedure FormCreate(Sender: TObject);
    procedure FormDestroy(Sender: TObject);
    procedure IdHTTPServer1CommandGet(AContext: TIdContext;
      ARequestInfo: TIdHTTPRequestInfo; AResponseInfo: TIdHTTPResponseInfo);
    procedure LazSerial1BlockSerialStatus(Sender: TObject;
      Reason: THookSerialReason; const Value: string);
    procedure LazSerial1RxData(Sender: TObject);
    procedure LazSerial1Status(Sender: TObject; Reason: THookSerialReason;
      const Value: string);
    procedure MenuItem1Click(Sender: TObject);
    procedure MenuItem2Click(Sender: TObject);
    procedure MenuItem3Click(Sender: TObject);
    procedure SdpoSerial1BlockSerialStatus(Sender: TObject;
      Reason: THookSerialReason; const Value: string);
    procedure SdpoSerial1RxData(Sender: TObject);
    procedure Timer1StartTimer(Sender: TObject);
    procedure Timer1StopTimer(Sender: TObject);
    procedure Timer1Timer(Sender: TObject);
  private
    Lbuffer: TStringList;
    procedure ListDev();
    function PegaSerial() : String;
    procedure SalvarContexto();
    procedure Setup();
    procedure getPage(aSocket : TLSocket; PeerAddress : string; mensagem: string);

    procedure SendData(uSVH: string; temp: string; hum: string; url: string);
  public

  end;

var
  frmmain: Tfrmmain;

implementation

{$R *.lfm}


procedure Tfrmmain.SendData(uSVH: string; temp: string; hum: string; url: string);
var
  lHTTP1: TFPHttpClient;
  lData: TStringList;
begin
  if (URL<> '') then
  begin
    lHTTP1 := TFPHttpClient.Create(nil);
    lData := TStringList.Create;
    try
      lHTTP1.AllowRedirect := True;

      lData.Add('uSVH=' + uSVH);
      lData.Add('temp=' + temp);
      lData.Add('hum=' + hum);

      lHTTP1.FormPost(url, lData);
    finally
      lData.Free;
      lHTTP1.Free;
    end;

  end;
end;


procedure Tfrmmain.FormCreate(Sender: TObject);
begin
  Lbuffer:= Tstringlist.create();
  lbuffer.clear;
  lbuffer.DelimitedText:=#13+#10;
  frmlog := TfrmLog.create(self);

  Fsetmain := TSetmain.create();
  self.left := Fsetmain.posx;
  self.top := fsetmain.posy;

  frmRegistrar := TfrmRegistrar.Create(self);
  frmRegistrar.Identifica();

  ListDev();
  lbVersao.Caption:= Version;
end;

procedure Tfrmmain.FormDestroy(Sender: TObject);
begin
  SalvarContexto();
  Lbuffer.free;
  Fsetmain.free();
  frmlog.free;
  frmRegistrar.free;

  //frmSetup.free;
end;

procedure Tfrmmain.IdHTTPServer1CommandGet(AContext: TIdContext;
  ARequestInfo: TIdHTTPRequestInfo; AResponseInfo: TIdHTTPResponseInfo);
var
  buffer: ansistring;
begin
 //  buffer :='HTTP/1.1 200 OK '+#10;
 //buffer := buffer +'Content-Type: text/html'+#10;
 //buffer := buffer + '<!DOCTYPE HTML>'+#10;
 buffer :=  '<html>'+#10;
 buffer := buffer + '<head>'+#10;
 buffer := buffer + '<title>Meu SRV</title>'+#10;
 buffer := buffer + '</head>'+#10;
 buffer := buffer + '<body>'+#10;
 buffer := buffer + '{';
 buffer := buffer + '"rs":{';
 buffer := buffer + '"Temperatura":' ;
 buffer := buffer + '"'+frmpeso.lbTemperatura.Caption+'"';
 buffer := buffer + '}';
 buffer := buffer + '}'+#10;
 buffer := buffer + '</body>'+#10;
 buffer := buffer + '</html>'+#10;
 AResponseInfo.ContentText := buffer;
end;

procedure Tfrmmain.LazSerial1BlockSerialStatus(Sender: TObject;
  Reason: THookSerialReason; const Value: string);
begin
  if(LazSerial1.Active) then
  begin
    //Shape1.Color:= clRed;
    lbstatus.Caption:= 'Open';
  end
  else
  begin
    //Shape1.Color:= clwhite;
    lbstatus.Caption:= 'close';
  end;
  Application.ProcessMessages();
end;

procedure Tfrmmain.LazSerial1RxData(Sender: TObject);
var
  info, parte: string;
  pos1, pos2: integer;
begin
  if (LazSerial1.DataAvailable) then
  begin
    info := LazSerial1.ReadData();
  end;

  Lbuffer.Text := Lbuffer.Text + info;

  for pos2 := 0 to Lbuffer.Count - 1 do
  begin
    parte := Lbuffer[pos2];

    pos1 := pos('Temperatura:', parte);
    if (pos1 <> 0) then
    begin
      frmpeso.Temperatura(Copy(parte, pos(':',parte) + 1, Length(parte)));
      Continue;
    end;

    pos1 := pos('Humidade:', parte);
    if (pos1 <> 0) then
    begin
      frmpeso.Humidade(Copy(parte, pos(':',parte) + 1, Length(parte)));
      Continue;
    end;

    pos1 := pos('CPM:', parte);
    if (pos1 <> 0) then
    begin
      frmpeso.Pulso(Copy(parte, pos(':',parte) + 1, Length(parte)));
      Continue;
    end;

    pos1 := pos('uSVH:', parte);
    if (pos1 <> 0) then
    begin
      frmpeso.Radiacao(Copy(parte, pos(':',parte) + 1, Length(parte)));
    end;
  end;

  Lbuffer.Clear;
  Application.ProcessMessages;
end;


procedure Tfrmmain.LazSerial1Status(Sender: TObject; Reason: THookSerialReason;
  const Value: string);
begin

end;



procedure Tfrmmain.MenuItem1Click(Sender: TObject);
begin
  show();
end;

procedure Tfrmmain.MenuItem2Click(Sender: TObject);
begin
       Setup();
end;

procedure Tfrmmain.MenuItem3Click(Sender: TObject);
begin
  frmPeso.show();
end;

procedure Tfrmmain.SdpoSerial1BlockSerialStatus(Sender: TObject;
  Reason: THookSerialReason; const Value: string);
begin

end;

procedure Tfrmmain.SdpoSerial1RxData(Sender: TObject);
begin
end;

procedure Tfrmmain.Timer1StartTimer(Sender: TObject);
begin
  lbstatus.Caption:= 'Lendo...';
end;

procedure Tfrmmain.Timer1StopTimer(Sender: TObject);
begin
 lbstatus.Caption:= 'Não Lendo';
end;

procedure Tfrmmain.Timer1Timer(Sender: TObject);
begin
  //LazSerial1.WriteData(#05);
  Application.ProcessMessages();

  SendData(frmPeso.lbRad.Caption,frmPeso.lbTemperatura.Caption, frmPeso.lbHumidade.caption , FSETMAIN.URL);

end;

procedure Tfrmmain.Button1Click(Sender: TObject);
begin
   //PegaSerial();
end;

procedure Tfrmmain.FormCloseQuery(Sender: TObject; var CanClose: boolean);
begin
  //if QuestionDlg('Sair?','Deseja sair? ',);
  canClose := false;
  hide;
  if(not TrayIcon1.Visible) then
  begin
     TrayIcon1.Visible:=true;
  end;
end;

procedure Tfrmmain.btConectarClick(Sender: TObject);
begin

  try
    if (Timer1.Enabled = false) then
    begin
      if (frmpeso= nil) then
      begin
          frmpeso := TFrmpeso.create(self);
      end;
      LazSerial1.close;
      LazSerial1.Device := FSETMAIN.COMPORT;
      LazSerial1.BaudRate:= TBaudRate(FSETMAIN.BAUDRATE);
      LazSerial1.DataBits:= TDataBits(FSETMAIN.DATABIT);
      //LazSerial1.FlowControl:= TFlowControl(FSETMAIN.;
      LazSerial1.Parity:= TParity(FSETMAIN.PARIDADE);
      LazSerial1.StopBits:= TStopBits(FSETMAIN.STOPBIT);
      LazSerial1.Open;
      frmpeso.show();
      Application.ProcessMessages();
      Timer1.Enabled:= true;
      TrayIcon1.Visible:=true;
      TrayIcon1.Hint:='Connected';
    end;
  finally
    hide;
  end;
end;

procedure Tfrmmain.btDesconectar1Click(Sender: TObject);
begin
  //SdpoSerial1.close;
  Timer1.Enabled:= false;
  LazSerial1.close;
  Application.ProcessMessages();
  if (frmpeso <> nil) then
  begin
    frmPeso.hide;
  end;
end;

procedure Tfrmmain.btlogClick(Sender: TObject);
begin
  frmLog.show;
end;

procedure Tfrmmain.btsairChange(Sender: TObject);
begin
  Application.Terminate;
end;

procedure Tfrmmain.btSetupClick(Sender: TObject);
begin
     btDesconectar1Click(self);
     Setup();
end;

procedure Tfrmmain.btTestaClick(Sender: TObject);
begin

end;

procedure Tfrmmain.ListDev();
begin
  //cbserial.Text :=  PegaSerial();
end;

function Tfrmmain.PegaSerial(): String;
var
  ListOfFiles: TStringList;
  Directory : string;
  posicao : integer;
begin


  ListOfFiles := TStringList.create();
  {$IFDEF LINUX}
  Directory := '/dev';
  FindAllFiles ( ListOfFiles , Directory ,  '*' ,  false ) ;
  posicao := 0;
  //ListOfFiles.Find('ttyS',posicao);
  //ListOfFiles.Sorted := true;
  cbserial.items.Clear;
  cbserial.Items.text:= ListOfFiles.Text;
  {$ENDIF}
end;

procedure Tfrmmain.SalvarContexto();
begin
  (*
  FSETMAIN.empresa := edEmpresa.text;
  FSETMAIN.Localizacao :=  edlocalizacao.text;
  FSETMAIN.Tipo1 :=  edTipo1.text;
  FSETMAIN.Tipo2 := edTipo2.text;
  FSETMAIN.Tipo3 := edTipo3.text;
  FSETMAIN.Contagem1 :=  strtoint(edCont1.text);
  FSETMAIN.Contagem2 := strtoint(edCont2.text);
  FSETMAIN.Contagem3 := strtoint( edCont3.text);
  *)
  FSETMAIN.posx := self.left;
  FSetMain.posy := self.top;
  (*
  FSetmain.painel:= edPainel.text;
  Fsetmain.tipoimp := cbTipoImp.ItemIndex;
  Fsetmain.modeloimp := cbModeImp.ItemIndex;
  *)
  //FSetmain.COMPORT := cbserial.text;
  (*
  Fsetmain.EXEC:= cbIniciar.Checked;
  *)
  FSETMAIN.SalvaContexto();

end;

procedure Tfrmmain.Setup();
begin
   frmsetup := Tfrmsetup.create(self);
 // frmSetup.edSerialPort.text := FSETMAIN.COMPORT;
  //192.168.0.105/wp-json/Geiser/v1/registro.php
  frmSetup.edSerialPort.text := FSETMAIN.COMPORT;
  frmSetup.cbBaudrate.ItemIndex:= FSETMAIN.BAUDRATE;
  frmSetup.cbDatabits.ItemIndex:= FSETMAIN.DATABIT;
  frmSetup.rgParity.ItemIndex:= FSETMAIN.PARIDADE;
  //frmSetup.rgFlowControl.ItemIndex:=FSETMAIN.;
  frmSetup.rgStopbit.ItemIndex := FSETMAIN.STOPBIT;
  frmSetup.edURL.text := FSETMAIN.URL;
  frmSetup.showmodal();
  frmsetup.free();
end;



procedure Tfrmmain.getPage(aSocket: TLSocket; PeerAddress: string;
  mensagem: string);
var
  buffer : WIDEstring;
begin

 //RespostaHTMLCabecalho(aSocket);

end;

end.

