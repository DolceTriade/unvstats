{ lib
, stdenv
, cmake
, pkg-config
, src
}:
stdenv.mkDerivation {
  pname = "crunch";
  version = "unstable";
  inherit src;

  nativeBuildInputs = [
    cmake
    pkg-config
  ];

  meta = with lib; {
    description = "Advanced DXT texture compression and transcoding tool";
    homepage = "https://github.com/DaemonEngine/crunch";
    license = licenses.zlib;
    platforms = platforms.linux ++ platforms.darwin;
  };
}
