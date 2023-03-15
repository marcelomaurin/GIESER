unit peso;

{$mode objfpc}{$H+}

interface

uses
  Classes, SysUtils, Forms, Controls, Graphics, Dialogs, StdCtrls, ExtCtrls,
  LedNumber, registro, funcoes, setmain;

type

  { TfrmPeso }

  TfrmPeso = class(TForm)
    Label2: TLabel;
    Label3: TLabel;
    Label4: TLabel;
    Label5: TLabel;
    Label6: TLabel;
    Label7: TLabel;
    Label8: TLabel;
    Label9: TLabel;
    lbRad: TLEDNumber;
    lbPulso: TLEDNumber;
    lbTemperatura: TLEDNumber;
    lbHumidade: TLEDNumber;
    Timer1: TTimer;
    procedure FormDestroy(Sender: TObject);
    procedure Timer1Timer(Sender: TObject);
  private

  public
    procedure Temperatura(info: string);
    procedure Humidade(info : string);
    procedure Radiacao(info : string);
    procedure Pulso(info: string);
    procedure Registrar();

  end;

var
  frmPeso: TfrmPeso;

implementation

{$R *.lfm}

{ TfrmPeso }

procedure TfrmPeso.Timer1Timer(Sender: TObject);
begin
  frmRegistrar.FIP:= GetLocalIPAddress;
  frmRegistrar.FLastDT:= datetimetostr(now);
  frmRegistrar.FNome:= FSETMAIN.MaqName;
  frmRegistrar.FLeitor:=FSETMAIN.MaqName;
  frmRegistrar.FHum:= lbHumidade.Caption;
  frmRegistrar.FRad:= lbRad.Caption;
  frmRegistrar.FTemp := lbTemperatura.Caption;
  frmRegistrar.FPulso:=lbPulso.Caption;
  frmRegistrar.Registra_log();
end;

procedure TfrmPeso.FormDestroy(Sender: TObject);
begin
   Timer1.Enabled:= true;
end;



procedure TfrmPeso.Temperatura(info: string);
begin
  lbTemperatura.Caption:= info;
  Application.ProcessMessages;
end;

procedure TfrmPeso.Humidade(info: string);
begin
  lbHumidade.Caption:= info;
  Application.ProcessMessages;
end;

procedure TfrmPeso.Radiacao(info: string);
begin
  lbRad.Caption:= info;
  Application.ProcessMessages;
end;

procedure TfrmPeso.Pulso(info: string);
begin
  lbPulso.Caption:= info;
  Application.ProcessMessages;
end;

procedure TfrmPeso.Registrar;
begin
   Timer1.Enabled:= true;
end;

end.

