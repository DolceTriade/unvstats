{ self, nixpkgs, flake-utils, inputs }:
let
  eachDefaultSystem = flake-utils.lib.eachDefaultSystem;
in
{
  packages = eachDefaultSystem (system:
    let
      pkgs = import nixpkgs { inherit system; };
    in
    import ./unvstats.nix {
      inherit pkgs inputs;
    });

  apps = eachDefaultSystem (system: {
    parser = {
      type = "app";
      program = "${self.packages.${system}.unvstats-parser}/bin/unvstats-parser";
    };
    default = self.apps.${system}.parser;
  });

  nixosModules.default = import ./module.nix { inherit self; };
}
