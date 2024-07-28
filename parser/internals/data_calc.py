# -*- coding: utf-8 -*-

# Main imports
import math

# External libraries
from externals.progressbar import ProgressBar, Percentage, Bar, ETA

""" Class: Calculator """


class Calculator:
    """Init Calculator"""

    def Main(self, dbc, players_to_update, all):
        # Localize dbc
        self.dbc = dbc

        # Here we do calculations, which are to heavy for real-time
        print("Calculating data ...")

        # Update the game time factor
        print(" - Calculating game time factor")
        self.dbc.execute(
            "SELECT `game_id` FROM `games` ORDER BY `game_id` DESC LIMIT 0, 1"
        )
        result = self.dbc.fetchone()
        if result != None:
            last_game = result[0]
        else:
            last_game = 1
        self.dbc.execute(
            """UPDATE `players` SET
		                    `player_game_time_factor` =
		                    POW( SQRT(`player_games_played`) /
		                         SQRT( %s - `player_first_game_id` + 1 ), 2 )""",
            (last_game,),
        )

        # Calculate efficiencies

        player_count = 0
        if all == True:
            self.dbc.execute(
                "SELECT `player_id` FROM `players` ORDER BY `player_id` DESC LIMIT 0, 1"
            )
            result = self.dbc.fetchone()
            if result != None:
                player_count = result[0]
        else:
            player_count = len(players_to_update)

        print(" - Calculating efficiency for %d players" % (player_count))

        # Start the progressbar
        try:
            pbar = ProgressBar(
                player_count, [Percentage(), " ", Bar(), " ", ETA()], fd=sys.stdout
            ).start()
        except:
            pbar = None

        # Iterate the stack
        player_id = 0
        while 1:
            if all == True:
                player_id += 1
                if player_id > player_count:
                    break
            else:
                if len(players_to_update) == 0:
                    break
                player_id = players_to_update.pop()
                if player_id == 0:
                    continue

            # Calculate kill- and destruction efficiency
            # Use of assists here is a little arbitrary
            self.dbc.execute(
                """UPDATE players SET
			                    `player_kill_efficiency` = (
			                      `player_kills` - `player_teamkills` +
			                      (`player_assists` / 2.5) - (`player_enemyassists` / 5) -
			                      ((`player_deaths_world_alien` + `player_deaths_world_human`) / 10)
			                    ) / (`player_deaths_enemy` + 1),

			                    `player_destruction_efficiency` = IFNULL((
			                      SELECT SUM(`building_efficiency_multiplier`)
			                      FROM `buildings`
			                      LEFT JOIN `destructions`
			                             ON `destruct_building_id` = `building_id`
			                            AND `destruct_player_id` = %s
			                      INNER JOIN `weapons`
			                             ON `weapon_id` = `destruct_weapon_id`
			                      WHERE `destruct_id` IS NOT NULL
			                            AND `weapon_team` != `building_team`
			                            AND `weapon_constant` != 'MOD_NOCREEP'
			                    ) / 5 / (`player_deaths_enemy` + 1), 0),

                                            `player_total_efficiency` = `player_kill_efficiency` + `player_destruction_efficiency`

			                    WHERE `player_id` = %s
			                 """,
                (player_id, player_id),
            )

            # Update the progressbar
            if pbar != None:
                try:
                    pbar.update(player_count - len(players_to_update))
                except:
                    pass

        # Finish the progressbar
        if pbar != None:
            try:
                pbar.finish()
            except:
                pass
