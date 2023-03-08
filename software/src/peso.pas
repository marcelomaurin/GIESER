unit peso;

{$mode objfpc}{$H+}

interface

uses
  Classes, SysUtils, Forms, Controls, Graphics, Dialogs, StdCtrls, LedNumber;

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
  private

  public
    procedure Temperatura(info: string);
    procedure Humidade(info : string);
    procedure Radiacao(info : string);
    procedure Pulso(info: string);

  end;

var
  frmPeso: TfrmPeso;

implementation

{$R *.lfm}

{ TfrmPeso }

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

end.

