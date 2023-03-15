unit funcoes;

{$mode objfpc}{$H+}

interface

uses
  Classes, SysUtils, Sockets,
  {$IFDEF LINUX}
    baseunix, unixtype
  {$ENDIF}
{$IFDEF WINDOWS}
   WinSock
{$ENDIF}
  ;

Function RetiraInfo(Value : string): string;
function BuscaChave( lista : TStringList; Ref: String; var posicao:integer): boolean;
function iif(condicao : boolean; verdade : variant; falso: variant):variant;
function GetIPAddress: string;
function GetLocalMachineName: string;

implementation

function GetLocalMachineName: string;
var
  HostName: string;
begin
  Result := '';
  SetLength(HostName, 255);
  // Obtém o nome do host local
  if GetHostName(PChar(HostName), 255) = 0 then
  begin
    Result := HostName;
  end;
end;

function GetLocalIPAddress: string;
var
  HostInfo: PHostEnt;
  HostName: string;
  i: Integer;
begin
  Result := '';
  HostName := '';
  // Obtém o nome do host local
  SetLength(HostName, 255);
  if GetHostName(PChar(HostName), 255) = 0 then
  begin
    HostInfo := GetHostByName(PChar(HostName));
    if HostInfo <> nil then
    begin
      i := 0;
      while HostInfo^.h_addr_list[i] <> nil do
      begin
        Result := inet_ntoa(PInAddr(HostInfo^.h_addr_list[i])^);
        Inc(i);
      end;
    end;
  end;
end;



function iif(condicao : boolean; verdade : variant; falso: variant):variant;
begin
     if condicao then
     begin
          result := verdade;
     end
     else
     begin
       result := falso
     end;
end;

//Retira o bloco de informação
Function RetiraInfo(Value : string): string;
var
  posicao : integer;
  resultado : string;
begin
     resultado := '';
     posicao := pos(':',value);
     if(posicao >-1) then
     begin
          resultado := copy(value,posicao+1,length(value));
     end;
     result := resultado;
end;

function BuscaChave( lista : TStringList; Ref: String; var posicao:integer): boolean;
var
  contador : integer;
  maximo : integer;
  item : string;
  indo : integer;
  resultado : boolean;
begin
     maximo := lista.Count-1;
     resultado := false;
     for contador := 0 to maximo do
     begin
       item := lista.Strings[contador];
       indo := pos(Ref,item);
       if (indo > 0) then
       begin
            posicao := contador;
            resultado := true;
            break;
       end;
     end;
     result := resultado;
end;

end.


