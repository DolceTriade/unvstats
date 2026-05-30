{ lib
, stdenvNoCC
}:
stdenvNoCC.mkDerivation {
  pname = "unvstats-web";
  version = "0.1.0";
  src = ../.;

  installPhase = ''
    runHook preInstall
    mkdir -p "$out/share/unvstats/web"
    cp -r web/. "$out/share/unvstats/web/"
    runHook postInstall
  '';

  meta = with lib; {
    description = "Unvstats PHP web frontend";
    platforms = platforms.linux;
  };
}
