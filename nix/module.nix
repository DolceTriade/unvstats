{self}: {
  config,
  lib,
  pkgs,
  ...
}: let
  inherit (lib) mkEnableOption mkIf mkOption mkDefault types optionalString escapeShellArgs;
  cfg = config.services.unvstats;

  escapePython = lib.replaceStrings ["\\" "'"] ["\\\\" "\\'"];
  escapePhp = escapePython;
  pythonList = values: "[${lib.concatMapStringsSep ", " (value: "'${escapePython value}'") values}]";

  parserConfigDir = pkgs.runCommand "unvstats-parser-config" {} ''
        mkdir -p "$out"
        cat > "$out/config.py" <<'EOF'
    # -*- coding: utf-8 -*-

    import os

    CONFIG = {}

    CONFIG['MYSQL_HOSTNAME'] = '${escapePython cfg.database.host}'
    CONFIG['MYSQL_USERNAME'] = '${escapePython cfg.database.user}'
    CONFIG['MYSQL_PASSWORD'] = open(os.environ.get('UNVSTATS_DB_PASSWORD_FILE', '${escapePython cfg.database.passwordFile}')).read().strip()
    CONFIG['MYSQL_DATABASE'] = '${escapePython cfg.database.name}'
    CONFIG['GAMES_LOG'] = '${escapePython cfg.parser.gamesLog}'
    CONFIG['DPK_DIR'] = ${pythonList cfg.parser.dpkDir}
    CONFIG['UNNAMED_PLAYER'] = ('UnnamedPlayer', 'Newbie#')
    EOF
  '';

  webConfigFile = pkgs.writeText "unvstats-config.inc.php" ''
    <?php
    define('MYSQL_HOSTNAME', '${escapePhp cfg.database.host}');
    define('MYSQL_USERNAME', '${escapePhp cfg.database.user}');
    define('MYSQL_PASSWORD', trim(file_get_contents(getenv('UNVSTATS_DB_PASSWORD_FILE') ?: '${escapePhp cfg.database.passwordFile}')));
    define('MYSQL_DATABASE', '${escapePhp cfg.database.name}');
    define('SERVER_ADDRESS', '${escapePhp cfg.web.serverAddress}');
    define('SERVER_NAME', '${escapePhp cfg.web.serverName}');
    define('TREMSTATS_TEMPLATE', '${escapePhp cfg.web.template}');
    define('TREMSTATS_SKIN', '${escapePhp cfg.web.skin}');
    define('TREMSTATS_EPP', ${toString cfg.web.entriesPerPage});
    define('GAMEPLAY_STATS_DIR', '${
      escapePhp (if cfg.web.gameplayStatsDir == null then "" else cfg.web.gameplayStatsDir)
    }');
    define('TRESHOLD_MIN_GAMES_PLAYED', ${toString cfg.web.minGamesPlayed});
    define('TRESHOLD_MAX_GAMES_PAUSED', ${toString cfg.web.maxGamesPaused});
    define('PRIVACY_LOGS', '${
      if cfg.web.privacy.logs
      then "1"
      else "0"
    }');
    define('PRIVACY_CHAT', '${
      if cfg.web.privacy.chat
      then "1"
      else "0"
    }');
    define('PRIVACY_QUOTE', '${
      if cfg.web.privacy.quote
      then "1"
      else "0"
    }');
    define('PRIVACY_NAME', '${
      if cfg.web.privacy.name
      then "1"
      else "0"
    }');
    ?>
  '';

  phpPackageDefault = pkgs.php83.buildEnv {
    extensions = {all, ...}:
      with all; [
        gd
        mysqli
      ];
  };

  mysqlPackageDefault = pkgs.mariadb;
  phpFpmSocket = "/run/phpfpm/unvstats.sock";
  webRoot = "${cfg.web.package}/share/unvstats/web";
in {
  options.services.unvstats = {
    enable = mkEnableOption "Unvstats";

    createUser = mkOption {
      type = types.bool;
      default = true;
      description = "Whether to create the dedicated Unvstats system user and group.";
    };

    user = mkOption {
      type = types.str;
      default = "unvstats";
      description = "User account for the parser and PHP-FPM pool.";
    };

    group = mkOption {
      type = types.str;
      default = "unvstats";
      description = "Group for the parser and PHP-FPM pool.";
    };

    database = {
      createLocally = mkOption {
        type = types.bool;
        default = true;
        description = "Whether to enable a local MariaDB/MySQL service for Unvstats.";
      };

      package = mkOption {
        type = types.package;
        default = mysqlPackageDefault;
        description = "MariaDB/MySQL package used when creating the database locally.";
      };

      host = mkOption {
        type = types.str;
        default = "127.0.0.1";
        description = "MySQL or MariaDB hostname for Unvstats.";
      };

      name = mkOption {
        type = types.str;
        default = "unvstats";
        description = "Database name for Unvstats.";
      };

      user = mkOption {
        type = types.str;
        default = "unvstats";
        description = "Database user for Unvstats.";
      };

      passwordFile = mkOption {
        type = types.path;
        description = "Path to a file containing only the database password.";
      };
    };

    parser = {
      enable = mkOption {
        type = types.bool;
        default = true;
        description = "Whether to enable the log parser service and timer.";
      };

      package = mkOption {
        type = types.package;
        default = self.packages.${pkgs.system}.unvstats-parser;
        description = "Packaged Unvstats parser.";
      };

      gamesLog = mkOption {
        type = types.str;
        description = "Absolute path to the Unvanquished games.log file.";
      };

      dpkDir = mkOption {
        type = types.listOf types.str;
        description = "Absolute paths to directories containing map .dpk files.";
      };

      extraArgs = mkOption {
        type = types.listOf types.str;
        default = [];
        description = "Extra arguments passed to the parser.";
      };

      timer = mkOption {
        type = types.str;
        default = "0/12:00:00";
        description = "systemd OnCalendar expression for parser runs.";
      };
    };

    web = {
      enable = mkOption {
        type = types.bool;
        default = true;
        description = "Whether to expose the PHP frontend through nginx and PHP-FPM.";
      };

      package = mkOption {
        type = types.package;
        default = self.packages.${pkgs.system}.unvstats-web;
        description = "Packaged Unvstats web root.";
      };

      hostName = mkOption {
        type = types.nullOr types.str;
        default = null;
        description = "nginx virtual host name for Unvstats.";
      };

      serverAliases = mkOption {
        type = types.listOf types.str;
        default = [];
        description = "Additional nginx server aliases.";
      };

      enableACME = mkOption {
        type = types.bool;
        default = false;
        description = "Whether to enable ACME on the Unvstats virtual host.";
      };

      forceSSL = mkOption {
        type = types.bool;
        default = false;
        description = "Whether to redirect HTTP to HTTPS on the Unvstats virtual host.";
      };

      phpPackage = mkOption {
        type = types.package;
        default = phpPackageDefault;
        description = "PHP package used for the Unvstats PHP-FPM pool.";
      };

      serverAddress = mkOption {
        type = types.str;
        default = "localhost:27960";
        description = "Address of the Unvanquished server shown by the UI.";
      };

      serverName = mkOption {
        type = types.str;
        default = "Unvanquished server";
        description = "Display name shown by the UI.";
      };

      template = mkOption {
        type = types.str;
        default = "default";
        description = "Template name for the UI.";
      };

      skin = mkOption {
        type = types.str;
        default = "default";
        description = "Skin name for the UI.";
      };

      entriesPerPage = mkOption {
        type = types.int;
        default = 50;
        description = "Entries per page in paginated views.";
      };

      gameplayStatsDir = mkOption {
        type = types.nullOr types.str;
        default = null;
        description = "Directory containing Format 3 gameplay stats logs named by MatchId.";
      };

      minGamesPlayed = mkOption {
        type = types.int;
        default = 25;
        description = "Minimum games required to appear in rankings.";
      };

      maxGamesPaused = mkOption {
        type = types.int;
        default = 500;
        description = "Maximum games since last activity before a player is hidden from rankings.";
      };

      privacy = {
        logs = mkOption {
          type = types.bool;
          default = false;
          description = "Hide the game log pages.";
        };

        chat = mkOption {
          type = types.bool;
          default = false;
          description = "Hide chat messages in game logs.";
        };

        quote = mkOption {
          type = types.bool;
          default = false;
          description = "Hide random quote output.";
        };

        name = mkOption {
          type = types.bool;
          default = false;
          description = "Hide alias names in player details.";
        };
      };
    };
  };

  config = mkIf cfg.enable {
    assertions = [
      {
        assertion = !cfg.web.enable || cfg.web.hostName != null;
        message = "services.unvstats.web.hostName must be set when the web UI is enabled.";
      }
      {
        assertion = !cfg.web.enable || config.services.nginx.enable;
        message = "services.nginx.enable must be true when services.unvstats.web.enable is true.";
      }
      {
        assertion = !cfg.database.createLocally || lib.elem cfg.database.host ["127.0.0.1" "localhost"];
        message = "services.unvstats.database.host must be localhost or 127.0.0.1 when createLocally is enabled.";
      }
    ];

    services.mysql = mkIf cfg.database.createLocally {
      enable = true;
      package = cfg.database.package;
    };

    users.groups = mkIf cfg.createUser {
      "${cfg.group}" = {};
    };

    users.users = mkIf cfg.createUser {
      "${cfg.user}" = {
        isSystemUser = true;
        group = cfg.group;
      };
    };

    systemd.services.unvstats-db-init = mkIf cfg.database.createLocally {
      description = "Initialize Unvstats MariaDB database";
      after = ["mysql.service"];
      requires = ["mysql.service"];
      before = ["unvstats-parser.service"];
      wantedBy = ["multi-user.target"];
      serviceConfig = {
        Type = "oneshot";
        User = "root";
      };
      script = ''
        db_password_sql="$(${pkgs.perl}/bin/perl -pe "s/\x27/\x27\x27/g" ${cfg.database.passwordFile})"

        ${cfg.database.package}/bin/mysql --protocol=socket -u root <<EOF
        CREATE DATABASE IF NOT EXISTS \`${cfg.database.name}\`;
        CREATE USER IF NOT EXISTS '${cfg.database.user}'@'localhost' IDENTIFIED BY '$db_password_sql';
        CREATE USER IF NOT EXISTS '${cfg.database.user}'@'127.0.0.1' IDENTIFIED BY '$db_password_sql';
        ALTER USER '${cfg.database.user}'@'localhost' IDENTIFIED BY '$db_password_sql';
        ALTER USER '${cfg.database.user}'@'127.0.0.1' IDENTIFIED BY '$db_password_sql';
        GRANT ALL PRIVILEGES ON \`${cfg.database.name}\`.* TO '${cfg.database.user}'@'localhost';
        GRANT ALL PRIVILEGES ON \`${cfg.database.name}\`.* TO '${cfg.database.user}'@'127.0.0.1';
        FLUSH PRIVILEGES;
        EOF

        if ! ${cfg.database.package}/bin/mysql --protocol=socket -u root -N -B \
          -e "USE \`${cfg.database.name}\`; SHOW TABLES LIKE 'games';" | ${pkgs.gnugrep}/bin/grep -q '^games$'; then
          ${cfg.database.package}/bin/mysql --protocol=socket -u root '${cfg.database.name}' < ${../sql/structure.sql}
          ${cfg.database.package}/bin/mysql --protocol=socket -u root '${cfg.database.name}' < ${../sql/data.sql}
        fi
      '';
    };

    systemd.services.unvstats-parser = mkIf cfg.parser.enable {
      description = "Unvstats log parser";
      after = ["network-online.target"] ++ lib.optionals cfg.database.createLocally ["mysql.service" "unvstats-db-init.service"];
      wants = ["network-online.target"] ++ lib.optionals cfg.database.createLocally ["mysql.service" "unvstats-db-init.service"];
      requires = lib.optionals cfg.database.createLocally ["mysql.service" "unvstats-db-init.service"];
      serviceConfig = {
        Type = "oneshot";
        User = cfg.user;
        Group = cfg.group;
        Environment = [
          "PYTHONPATH=${parserConfigDir}:${cfg.parser.package}/lib/unvstats/parser"
          "UNVSTATS_DB_PASSWORD_FILE=${cfg.database.passwordFile}"
        ];
      };
      script = ''
        exec ${cfg.parser.package}/bin/unvstats-parser ${escapeShellArgs cfg.parser.extraArgs}
      '';
    };

    systemd.timers.unvstats-parser = mkIf cfg.parser.enable {
      description = "Schedule Unvstats log parsing";
      wantedBy = ["timers.target"];
      timerConfig = {
        OnCalendar = cfg.parser.timer;
        Persistent = true;
      };
    };

    services.phpfpm.pools.unvstats = mkIf cfg.web.enable {
      user = cfg.user;
      group = cfg.group;
      phpPackage = cfg.web.phpPackage;
      settings = {
        "listen" = phpFpmSocket;
        "listen.owner" = config.services.nginx.user;
        "listen.group" = config.services.nginx.group;
        "listen.mode" = "0660";
        "pm" = "dynamic";
        "pm.max_children" = 8;
        "pm.start_servers" = 2;
        "pm.min_spare_servers" = 1;
        "pm.max_spare_servers" = 4;
        "catch_workers_output" = "yes";
        "clear_env" = "no";
        "env[UNVSTATS_WEB_CONFIG]" = "${webConfigFile}";
        "env[UNVSTATS_DB_PASSWORD_FILE]" = "${cfg.database.passwordFile}";
      };
    };
    services.nginx.virtualHosts = mkIf (cfg.web.enable && cfg.web.hostName != null) {
      "${cfg.web.hostName}" = {
        enableACME = cfg.web.enableACME;
        forceSSL = cfg.web.forceSSL;
        serverAliases = cfg.web.serverAliases;
        root = webRoot;
        locations."/" = {
          index = "index.php";
          tryFiles = "$uri $uri/ /index.php?$query_string";
        };
        locations."~ \\.php$" = {
          extraConfig = ''
            include ${pkgs.nginx}/conf/fastcgi.conf;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_pass unix:${phpFpmSocket};
          '';
        };
      };
    };
  };
}
