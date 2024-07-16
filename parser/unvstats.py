#!/usr/bin/python
# -*- coding: utf-8 -*-

"""
" Project:     Unvstats
" File:        unvstats.py
"
" This program is free software; you can redistribute it and/or
" modify it under the terms of the GNU Lesser General Public
" License as published by the Free Software Foundation; either
" version 2.1 of the License, or (at your option) any later version.
"
" This program is distributed in the hope that it will be useful,
" but WITHOUT ANY WARRANTY; without even the implied warranty of
" MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
" Lesser General Public License for more details.
"
" You should have received a copy of the GNU Lesser General Public
" License along with this library; if not, write to the Free Software
" Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
"
" @link http://tremstats.dasprids.de/
" @author Ben 'DASPRiD' Scholzen <mail@dasprids.de>
" @package Tremstats
" @version 0.6.0 ~ slux`s Mod <slux83@gmail.com>
"""

# Main imports
import sys, os

# Additional site-packages
import MySQLdb

# Internal libraries
from internals.log_parse import Parser
from internals.data_calc import Calculator
from internals.skill_calc import Skills
from internals.dpk_read import Reader

# Config
from config import CONFIG

""" Mainclass: Unvstats """
class Unvstats:
	""" Init Unvstats """
	def Main(self):
		# Internal datas
		self.games_log         = CONFIG['GAMES_LOG']
		self.dpk_dir           = CONFIG['DPK_DIR']
		self.calconly          = False
		self.pk3only           = False
		self.parseonly         = False
		self.logsonly          = False
		self.one_pk3           = None
		self.reparse           = False
		self.clear_ids         = False
		self.maps              = {}
		self.players_to_update = []

		# Check for command line arguments
		self.Check_command_line_arguments()

		# Connect to MySQL
		self.MySQL_connect()

		# Single pk3
		if self.one_pk3 != None:
			pk3reader = Reader()
			pk3reader.Main(self.dbc, self.Check_map_in_database, None, self.one_pk3)
			return

		# Read pk3 map files
		if self.calconly == False and self.logsonly == False:
			pk3reader = Reader()
			pk3reader.Main(self.dbc, self.Check_map_in_database, self.dpk_dir, None)
			if self.pk3only == True:
				return

		if self.logsonly == True:
			self.calconly  = False
			self.parseonly = False

		# Check for reparsing
		if self.reparse == True:
			# Set variables for reparsing
			self.calconly  = False
			self.parseonly = False

			print "clearing database and reparsing entire log..."

			# Clear the database
			self.dbc.execute("TRUNCATE `builds`")
			self.dbc.execute("TRUNCATE `decons`")
			self.dbc.execute("TRUNCATE `destructions`")
			self.dbc.execute("TRUNCATE `kills`")
			self.dbc.execute("TRUNCATE `games`")
			self.dbc.execute("TRUNCATE `map_stats`")
			self.dbc.execute("TRUNCATE `per_game_stats`")
			self.dbc.execute("TRUNCATE `says`")
			self.dbc.execute("TRUNCATE `votes`")
			self.dbc.execute("TRUNCATE `state`")

			if self.clear_ids == True:
				print "clearing player ids..."
				self.dbc.execute("TRUNCATE `players`")
				self.dbc.execute("TRUNCATE `skill`")
				self.dbc.execute("TRUNCATE `nicks`")
			else:
				print "NOT clearing player ids, if this was desired use --clear-ids"
				self.dbc.execute("""UPDATE `players` SET
				                    player_games_played = DEFAULT,
				                    player_first_game_id = DEFAULT,
				                    player_first_gametime = DEFAULT,
				                    player_last_game_id = DEFAULT,
				                    player_last_gametime = DEFAULT,
				                    player_game_time_factor = DEFAULT,
				                    player_kill_efficiency = DEFAULT,
				                    player_destruction_efficiency = DEFAULT,
				                    player_total_efficiency = DEFAULT,
				                    player_kills = DEFAULT,
				                    player_kills_alien = DEFAULT,
				                    player_kills_human = DEFAULT,
				                    player_teamkills = DEFAULT,
				                    player_teamkills_alien = DEFAULT,
				                    player_teamkills_human = DEFAULT,
				                    player_deaths = DEFAULT,
				                    player_deaths_enemy = DEFAULT,
				                    player_deaths_enemy_alien = DEFAULT,
				                    player_deaths_enemy_human = DEFAULT,
				                    player_deaths_team_alien = DEFAULT,
				                    player_deaths_team_human = DEFAULT,
				                    player_deaths_world_alien = DEFAULT,
				                    player_deaths_world_human = DEFAULT,
				                    player_time_alien = DEFAULT,
				                    player_time_human = DEFAULT,
				                    player_time_spec = DEFAULT,
				                    player_score_total = DEFAULT""")

		# Parse log
		if self.calconly == False:
			parser = Parser()
			result = parser.Main(self.dbc, self.Check_map_in_database, self.Add_player_to_update, self.games_log)
			if result == None:
				# nothing parsed, exit fast
				self.parseonly = True

		# Calculate data out of the parsed log
		if self.parseonly == False:
			calculator = Calculator()
			calculator.Main(self.dbc, self.players_to_update, self.calconly)

                        skills = Skills()
                        skills.Main(self.dbc)

	""" Check command line arguments """
	def Check_command_line_arguments(self):
		args = sys.argv[1:]
		for arg in args:
			arg_data = arg.split('=', 1)

			if len(arg_data) == 1:
				if arg_data[0] == '--help':
					print "Usage of unvstats.py:"
					print "----------------------------------------------------"
					print "--help:        Print this help"
					print "--reparse:     Reparses all archived logs"
					print "--clear-ids:   Clear player ids when reparsing logs"
					print "--calconly:    Only calculate data for MySQL"
					print "--parseonly:   Only parse the log file (debugging)"
					print "--pk3only:     Only fetch data from PK3s"
					print "--log=<file>:  Parse another log than default"
					print "--pk3=<dir>:   Read another dir than default"
					print "--map=<file>:  Parse a single map pk3 for levelshot"
					print "--db=<name>:   Specify database name"
					print "--pw=<pass>:   Specify database password"
					sys.exit(-1)

				elif arg_data[0] == '--calconly':
					self.calconly = True
				elif arg_data[0] == '--parseonly':
					self.parseonly = True
				elif arg_data[0] == '--logsonly':
					self.logsonly = True
				elif arg_data[0] == '--pk3only':
					self.pk3only = True
				elif arg_data[0] == '--reparse':
					self.reparse = True
				elif arg_data[0] == '--clear-ids':
					self.clear_ids = True
				else:
					sys.exit("Invalid arguments, see `unvstats.py --help`")
			elif len(arg_data) == 2:
				if arg_data[0] == '--log':
					self.games_log  = arg_data[1]
					self.static_log = True
				elif arg_data[0] == '--pk3':
					self.dpk_dir = arg_data[1]
				elif arg_data[0] == '--map':
					self.one_pk3 = arg_data[1]
				elif arg_data[0] == '--db':
					CONFIG['MYSQL_DATABASE'] = arg_data[1]
				elif arg_data[0] == '--pw':
					CONFIG['MYSQL_PASSWORD'] = arg_data[1]
				else:
					sys.exit("Invalid arguments, see `unvstats.py --help`")

	""" Connect to MySQL """
	def MySQL_connect(self):
		# Try to connect to MySQL, else exit
		try:
			self.db = MySQLdb.connect(CONFIG['MYSQL_HOSTNAME'], CONFIG['MYSQL_USERNAME'], CONFIG['MYSQL_PASSWORD'], CONFIG['MYSQL_DATABASE'])
			self.dbc = self.db.cursor()
		except:
			sys.exit("Connection to MySQL failed")

	""" Check if a specific map exists in the database """
	def Check_map_in_database(self, mapname):
		# Check internal dict first
		if self.maps.has_key(mapname):
			return self.maps[mapname]

		# Not in internal dict, check database
		self.dbc.execute("SELECT `map_id` FROM `maps` WHERE `map_name` = %s", (mapname, ))
		result = self.dbc.fetchone()

		# If map does not exist yet, insert it
		if result == None:
			self.dbc.execute("INSERT INTO `maps` (`map_name`) VALUES (%s)", (mapname, ))
			self.dbc.execute("SELECT LAST_INSERT_ID()")
			result = self.dbc.fetchone()

		# Return map id
		map_id = result[0]

		self.maps[mapname] = map_id
		return map_id

	""" Add a player to the update stack """
	def Add_player_to_update(self, player_id):
		if self.players_to_update.count(player_id) == 0:
			self.players_to_update.append(player_id)


""" Init Application """
if __name__ == '__main__':
	app = Unvstats()
	app.Main()
