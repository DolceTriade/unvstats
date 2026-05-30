{ lib
, stdenvNoCC
, makeWrapper
, python3
, libwebp
, crunch
}:
let
  python = python3.withPackages (ps: with ps; [
    mysqlclient
    pillow
  ]);
in
stdenvNoCC.mkDerivation {
  pname = "unvstats-parser";
  version = "0.1.0";
  src = ../.;

  nativeBuildInputs = [ makeWrapper ];

  installPhase = ''
    runHook preInstall

    mkdir -p "$out/bin" "$out/lib/unvstats/parser"
    cp -r parser/. "$out/lib/unvstats/parser/"
    chmod +x "$out/lib/unvstats/parser/unvstats.py"

    makeWrapper ${python}/bin/python "$out/bin/unvstats-parser" \
      --add-flags "$out/lib/unvstats/parser/unvstats.py" \
      --set-default PYTHONPATH "$out/lib/unvstats/parser" \
      --suffix PATH : "${lib.makeBinPath [ crunch libwebp ]}"

    runHook postInstall
  '';

  meta = with lib; {
    description = "Unvstats log parser";
    platforms = platforms.linux;
  };
}
