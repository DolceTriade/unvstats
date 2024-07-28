#!/bin/bash

echo "Unvstats simple setup script"
echo "you will be prompted for these things:"
echo "  mysql host, username, password"
echo "  mysql name to use for Unvstats database"
echo "  ip:port of server (optional, for server status)"
echo "  full path to games.log and map pk3 files"
echo "  full path to copy website files"
echo "  full path to copy unvstats log parser"
echo ""
read -p "press enter to continue" INPUT
echo ""

if [ ! -f "sql/data.sql" ] || [ ! -f "parser/unvstats.py" ] ; then
  echo "Sorry, this script must be run from within"
  echo " the Unvstats package directory (as ./setup.sh)"
  exit
fi

#i defaults:
MYSQL_HOST="localhost"
MYSQL_USER=""
MYSQL_PASS=""
MYSQL_NAME="unvstats"

SERVER_IP="localhost:27960"
SERVER_NAME="Unnamed Server"

PATH_LOG="$HOME/.Unvanquished/main/games.log"
PATH_MAPS="$HOME/unvanquished/main"
PATH_WEBSITE="/var/www/html"
PATH_PARSER="$HOME/unvstats"
COPY_WEBSITE="no"
COPY_PARSER="no"


STATE_FILE=setup.state

if [ -f $STATE_FILE ] ; then
  source $STATE_FILE
fi


function request {
  INPUT=""
  RESULT="${3}"
  MESSAGE="${1} (${RESULT}) ]"
  read -p "$MESSAGE" INPUT

  if [ "x$INPUT" != "x" ] ; then
    RESULT="$INPUT"
  else
    echo "using default value"
  fi
  if [ "x$RESULT" == "x" ] ; then
    echo "No value entered, aborting setup"
    exit
  fi
  echo "${2} = '${RESULT}'"
  echo ""
  return 0
}

function savestate {
  # write out in shell-safe form
  exec 3>"$STATE_FILE"

  printf >&3 'MYSQL_HOST=%q\n'   "$MYSQL_HOST"
  printf >&3 'MYSQL_USER=%q\n'   "$MYSQL_USER"
  printf >&3 'MYSQL_PASS=%q\n'   "$MYSQL_PASS"
  printf >&3 'MYSQL_NAME=%q\n'   "$MYSQL_NAME"

  printf >&3 'SERVER_IP=%q\n'    "$SERVER_IP"
  printf >&3 'SERVER_NAME=%q\n'  "$SERVER_NAME"

  printf >&3 'PATH_LOG=%q\n'     "$PATH_LOG"
  printf >&3 'PATH_MAPS=%q\n'    "$PATH_MAPS"
  printf >&3 'PATH_WEBSITE=%q\n' "$PATH_WEBSITE"
  printf >&3 'PATH_PARSER=%q\n'  "$PATH_PARSER"
  printf >&3 'COPY_WEBSITE=%q\n' "$COPY_WEBSITE"
  printf >&3 'COPY_PARSER=%q\n'  "$COPY_PARSER"

  exec 3>&-
}

echo "MYSQL information"
echo ""

request "Enter mysql hostname" "mysql hostname" "$MYSQL_HOST"
MYSQL_HOST="$RESULT"

request "Enter mysql username" "mysql username" "$MYSQL_USER"
MYSQL_USER="$RESULT"

request "Enter mysql password" "mysql password" "$MYSQL_PASS"
MYSQL_PASS="$RESULT"

request "Enter mysql database" "mysql database name" "$MYSQL_NAME"
MYSQL_NAME="$RESULT"

echo "Unvanquished server information"
echo ""

request "Enter path to games.log" "games.log" "$PATH_LOG"
PATH_LOG="$RESULT"

request "Enter path to map pk3 files" "map pk3 files" "$PATH_MAPS"
PATH_MAPS="$RESULT"

request "Enter Unvanquished server ip" "unv server ip" "$SERVER_IP"
SERVER_IP="$RESULT"

request "Enter Unvanquished server name" "unv server name" "$SERVER_NAME"
SERVER_NAME="$RESULT"

echo "INSTALLATION paths"
echo ""

request "Enter path to install website files" "www path" "$PATH_WEBSITE"
PATH_WEBSITE="$RESULT"

echo "note: if you do not have permissions to write $PATH_WEBSITE,"
echo "say no here, and copy the 'web' directory to the desired location"

request "Do you want to install website files [yes/no]" "copy www" "$COPY_WEBSITE"
COPY_WEBSITE="$RESULT"

request "Enter path to install unvstats.py" "parser path" "$PATH_PARSER"
PATH_PARSER="$RESULT"

echo "note: if you do not have permissions to write $PATH_PARSER,"
echo "say no here, and copy the 'parser' directory to the desired location"

request "Do you want to install unvstats.py files [yes/no]" "copy parser" "$COPY_PARSER"
COPY_PARSER="$RESULT"

savestate

echo "Ok, I am now about to verify the mysql information entered above"
echo "by logging in with mysql and running a STATUS command"
read -p "Press enter to continue" INPUT

echo "testing mysql information..."
mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASS" -e STATUS
MYSQL_OK=$?
if [ "$MYSQL_OK" != "0" ] ; then
  echo "ERROR communicating with mysql host, please check entered values"
  echo "installation aborted"
  exit
fi

echo "Good! mysql seems to be ok"
echo ""
echo "I will now (optionally) create and initialize the database '$MYSQL_NAME'"
echo "NOTE: if a database named '$MYSQL_NAME' exists, this will fail"
echo "Enter 'yes' to create the database '$MYSQL_NAME' or 'no' to skip this step."
read -p "Create database ? [yes/no] ]" INPUT
if [ "$INPUT" == "yes" ] ; then
  echo "Creating database..."
  mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASS" -e "CREATE DATABASE $MYSQL_NAME"
  MYSQL_OK=$?
  if [ "$MYSQL_OK" != "0" ] ; then
    echo "I got an error trying to create '$MYSQL_NAME'"
    echo "installation aborted"
    exit
  fi
else
  echo " ** that was not a 'yes', database creation SKIPPED."
fi

echo "Enter 'yes' to setup the database structure for '$MYSQL_NAME' or 'no' to skip this step."
echo "NOTE: by saying 'yes', any existing unvstats data in database '$MYSQL_NAME' will be lost"
read -p "Setup database structure ? [yes/no] ]" INPUT
if [ "$INPUT" == "yes" ] ; then
  echo "setting up essential data..."
  mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASS" "$MYSQL_NAME" < sql/structure.sql
  mysql -h "$MYSQL_HOST" -u "$MYSQL_USER" -p"$MYSQL_PASS" "$MYSQL_NAME" < sql/data.sql
  echo "database is ready, continuing"
else
  echo " ** that was not a 'yes', database creation SKIPPED."
fi

echo ""

Quote()
{
  echo "-- $*" | sed -e 's/^-- //; s/\\/\\\\/g; s/'\''/\\'\''/g'
}

PARSER_CONFIG="parser/config.py"
echo "updating file '$PARSER_CONFIG'..."
test -f "$PARSER_CONFIG" && cp -af "$PARSER_CONFIG" "$PARSER_CONFIG".bak
test -f "$PARSER_CONFIG" -a -s "$PARSER_CONFIG"  || cp -af "$PARSER_CONFIG".default "$PARSER_CONFIG"
sed -e '/CONFIG.*MYSQL_HOSTNAME/ s!= .*$!= '"'$(Quote "$MYSQL_HOST")'"'!g;
	/CONFIG.*MYSQL_USERNAME/ s!= .*$!= '"'$(Quote "$MYSQL_USER")'"'!g;
	/CONFIG.*MYSQL_PASSWORD/ s!= .*$!= '"'$(Quote "$MYSQL_PASS")'"'!g;
	/CONFIG.*MYSQL_DATABASE/ s!= .*$!= '"'$(Quote "$MYSQL_NAME")'"'!g;
	/CONFIG.*GAMES_LOG/      s!= .*$!= '"'$(Quote "$PATH_LOG")'"'!g;
	/CONFIG.*DPK_DIR/        s!= .*$!= '"'$(Quote "$PATH_MAPS")'"'!g;
' <"$PARSER_CONFIG.default" >"$PARSER_CONFIG"

WEB_CONFIG="web/core/config.inc.php"
echo "updating file '$WEB_CONFIG'..."
test -f "$WEB_CONFIG" && cp -af "$WEB_CONFIG" "$WEB_CONFIG".bak
test -f "$WEB_CONFIG" -a -s "$WEB_CONFIG" || cp -af "$WEB_CONFIG".default "$WEB_CONFIG"
sed -e '/define.*MYSQL_HOSTNAME/ s!, .*$!, '"'$(Quote "$MYSQL_HOST")'"');!g;
	/define.*MYSQL_USERNAME/ s!, .*$!, '"'$(Quote "$MYSQL_USER")'"');!g;
	/define.*MYSQL_PASSWORD/ s!, .*$!, '"'$(Quote "$MYSQL_PASS")'"');!g;
	/define.*MYSQL_DATABASE/ s!, .*$!, '"'$(Quote "$MYSQL_NAME")'"');!g;
	/define.*SERVER_ADDRESS/ s!, .*$!, '"'$(Quote "$SERVER_IP")'"');!g;
	/define.*SERVER_NAME/    s!, .*$!, '"'$(Quote "$SERVER_NAME")'"');!g;
' <"$WEB_CONFIG.default" >"$WEB_CONFIG"

if [ "$COPY_WEBSITE" == "yes" ] ; then
  echo "Copying 'web' directory to $PATH_WEBSITE"
  mkdir -pv "$PATH_WEBSITE"
  cp -r web/* "$PATH_WEBSITE"
else
  echo "Copying 'web' skipped by request"
  echo "  ** update $PATH_WEBSITE/core/config.in.php manually"
fi

if [ "$COPY_PARSER" == "yes" ] ; then
  echo "Copying 'parser' to $PATH_PARSER"
  mkdir -pv "$PATH_PARSER"
  cp -r parser/* "$PATH_PARSER/."
else
  echo "Copying 'parser' skipped by request"
  echo "  ** update $PATH_PARSER/config.py manually"
fi

echo ""
echo "important locations:"
echo " parser config = $PATH_PARSER/config.py"
echo "               *default is in parser/config.py.default"
echo " web configs   = $PATH_WEBSITE/core/config.inc.php"
echo "               *default is in web/core/config.inc.php.default"
echo " games.log     = $PATH_LOG"
echo " maps          = $PATH_MAPS"
echo " database name = $MYSQL_NAME"
echo ""
echo "Unvstats setup script complete."

