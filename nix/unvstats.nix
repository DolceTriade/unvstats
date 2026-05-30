{ pkgs, inputs }:
rec {
  crunch = pkgs.callPackage ./crunch.nix {
    src = inputs.crunchSrc;
  };

  unvstats-parser = pkgs.callPackage ./parser.nix {
    inherit crunch;
  };

  unvstats-web = pkgs.callPackage ./web.nix {};

  default = unvstats-web;
}
