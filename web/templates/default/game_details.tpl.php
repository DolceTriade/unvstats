<?php include '__header__.tpl.php'; ?>

<section>
  <header>
    <h2 class="heading">Game #<?php echo $this->game_details['game_id']; ?> Summary</h2>
    <div class="headinglink"> ( <a href="game_log.php?game_id=<?php echo $this->game_details['game_id'] ?>">show game log</a> | <a href="<?php echo htmlspecialchars($this->include_bots_toggle_url, ENT_QUOTES); ?>"><?php echo $this->include_bots ? 'hide bots' : 'include bots'; ?></a> )</div>
  </header>

  <table>
    <colgroup>
      <col class="levelshot" />
      <col class="item" />
      <col />
    </colgroup>

    <thead>
      <tr><th colspan="3">Game info</th></tr>
    </thead>

    <tbody>
     <tr>
      <td rowspan="10" class="levelshot">
        <img class="levelshot" alt="<?php echo htmlspecialchars($this->map['game_map_name'],ENT_QUOTES); ?>" src="_levelshot.php?map_id=<?php echo ($this->game_details['game_map_id']); ?>" />
      </td>
      <td><strong>Map Name</strong></td>
      <td><strong><a href="map_details.php?map_id=<?php echo $this->game_details['game_map_id'] ; ?>"><?php echo replace_color_codes($this->map['game_map_name']); ?></a></strong></td>
     </tr>
     <tr>
      <td>Winner</td>
      <td><?php echo $this->game_details['game_winner']; ?></td>
     </tr>
     <tr>
      <td>Game time</td>
      <td><?php echo $this->game_details['game_length']; ?></td>
     </tr>
     <tr>
      <td>Date <small>(UTC)</small></td>
      <td><?php echo $this->game_details['game_timestamp']; ?></td>
     </tr>
    </tbody>
  </table>
<?php $tables = array(array('Aliens', 'alien'), array('Humans', 'human'));
      foreach ($tables as $table)
      {
?>
  <table>
    <colgroup>
      <col class="playername" />
      <col class="data" />
      <col class="data" />
      <col class="data" />
      <col class="data" />
      <col class="data" />
      <col class="data" />
      <col class="data" />
    </colgroup>

    <thead>
      <tr class="<?php echo $table[1]; ?>s-teamshader">
        <th><?php echo $table[0]; ?></th>
        <th>Score</th>
        <th>Kills</th>
        <th>Assists</th>
        <th>Team Kills</th>
        <th>Enemy Assists</th>
        <th>Deaths</th>
        <th>Time</th>
      </tr>
    </thead>

    <tbody>
      <?php $count = false; $time = 'time_'.$table[1];
            foreach ($this->players as $player) { ?>
        <?php if ($player[$time]) { ?>
      <tr class="list" >
        <td class="playername"><?php echo player_link($player['player_id'], $player['player_name']) ?><?php if (!empty($player['player_is_bot'])): ?> <span class="bot">bot</span><?php endif; ?></td>
        <td><?php echo $player['stats_score'] ?></td>
        <td><?php echo $player['stats_kills'] ?></td>
        <td><?php echo $player['stats_assists'] ?></td>
        <td><?php echo $player['stats_teamkills'] ?></td>
        <td><?php echo $player['stats_enemyassists'] ?></td>
        <td><?php echo $player['stats_deaths'] ?></td>
        <td><?php echo $player[$time] ?></td>
      </tr>
        <?php   $count = true;
              }
            }
            if (!$count) { ?>
        <tr class="emptylist">
          <td colspan="8">No players</td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
<?php } ?>

  <table>
    <colgroup>
      <col class="playername" />
      <col class="data" /><col class="data" /><col class="data" /><col class="data" /><!-- dummy columns for width calculation -->
      <col class="data" />
    </colgroup>

    <thead>
      <tr class="spectators-teamshader">
        <th colspan="5">Spectators</th>
        <th>Time</th>
      </tr>
    </thead>

    <tbody>
      <?php $count = false; foreach ($this->players as $player) { ?>
        <?php if ($player['time_spec'] && !$player['time_human'] && !$player['time_alien']) { ?>
      <tr class="list">
        <td class="playername" colspan="5"><?php echo player_link($player['player_id'], $player['player_name']) ?><?php if (!empty($player['player_is_bot'])): ?> <span class="bot">bot</span><?php endif; ?></td>
        <td><?php echo $player['time_spec'] ?></td>
      </tr>
        <?php   $count = true;
              }
            }
            if (!$count) { ?>
        <tr class="emptylist">
          <td colspan="6">None</td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col />
    </colgroup>

    <thead>
      <tr>
        <th>Stats per minute</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td><?php graph_killsInGame($this->game_details['game_id'], 'Nothing of interest… short game…', 'Wow, like really peaceful, man!'); ?></td>
      </tr>
    </tbody>
  </table>

  <?php if (!empty($this->gameplay_stats) && (!empty($this->gameplay_stats['samples']) || !empty($this->gameplay_stats['events']))): ?>
  <table>
    <colgroup>
      <col class="data" />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th colspan="2">Detailed Gameplay Stats</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td>Match ID</td>
        <td><?php echo htmlspecialchars($this->gameplay_stats['match_id'], ENT_QUOTES); ?></td>
      </tr>
      <?php if (!$this->include_bots): ?>
      <tr>
        <td>Note</td>
        <td>Gameplay stats are shown from the raw match file and may still include bot-only events.</td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col class="data" />
      <col class="data" />
      <col class="data" />
      <col class="data" />
      <col class="data" />
      <col class="data" />
      <col class="data" />
    </colgroup>

    <thead>
      <tr>
        <th>T</th>
        <th>#A</th>
        <th>#H</th>
        <th>ANet</th>
        <th>HNet</th>
        <th>ASpent</th>
        <th>HSpent</th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($this->gameplay_stats['samples'] as $sample): ?>
      <tr class="list">
        <td><?php echo $sample['t']; ?></td>
        <td><?php echo $sample['aliens']; ?></td>
        <td><?php echo $sample['humans']; ?></td>
        <td><?php echo $sample['alien_net']; ?></td>
        <td><?php echo $sample['human_net']; ?></td>
        <td><?php echo $sample['alien_spent']; ?></td>
        <td><?php echo $sample['human_spent']; ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col class="data" />
      <col class="data" />
      <col class="data" />
      <col />
      <col class="data" />
    </colgroup>

    <thead>
      <tr>
        <th>T</th>
        <th>Team</th>
        <th>Client</th>
        <th>Event</th>
        <th>Cost</th>
      </tr>
    </thead>

    <tbody>
      <?php if (empty($this->gameplay_stats['events'])): ?>
      <tr class="emptylist">
        <td colspan="5">No gameplay events</td>
      </tr>
      <?php else: ?>
      <?php foreach ($this->gameplay_stats['events'] as $event): ?>
      <tr class="list">
        <td><?php echo $event['t']; ?></td>
        <td><?php echo htmlspecialchars($event['team'], ENT_QUOTES); ?></td>
        <td><?php echo $event['client']; ?></td>
        <td><?php echo htmlspecialchars($event['kind'].' '.$event['item'], ENT_QUOTES); ?></td>
        <td><?php echo $event['cost']; ?></td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
  <?php endif; ?>

</section>

 <?php include '__footer__.tpl.php'; ?>
