{
  description = "Nix flake for Unvstats with nginx and PHP-FPM integration";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
    flake-utils.url = "github:numtide/flake-utils";
    crunchSrc = {
      url = "github:DaemonEngine/crunch";
      flake = false;
    };
  };

  outputs = inputs @ { self, nixpkgs, flake-utils, ... }:
    import ./nix/flake-outputs.nix {
      inherit self nixpkgs flake-utils inputs;
    };
}
