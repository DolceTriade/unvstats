<?php include '__header__.tpl.php'; ?>

<div>
  <div class="heading">
    <h2>Player Details for <?php echo replace_color_codes($this->player_details['player_name']); ?></h2>
    <div class="headinglink"> ( <a href="player_getsig.php?player_id=<?php echo $this->player_details['player_id'] ?>">get a player signature</a> )</div>
  </div>

<?php if ($this->player_details['player_is_bot']) { ?>
  <table>
    <thead>
      <tr>
        <th >This player is a bot.</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
<?php } else { ?>
  <table>
    <colgroup>
      <col class="item" />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th colspan="2">General Stats</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <th>Games</th>
        <td><?php echo $this->player_details['player_games_played']; ?></td>
      </tr>
      <tr>
        <th>Aggregate Score</th>
        <td><?php echo $this->player_details['player_score_total']; ?></td>
      </tr>
      <tr>
        <th>First seen</th>
        <td><?php echo $this->player_details['player_first_seen']; ?></td>
      </tr>
      <tr>
        <th>Last seen</th>
        <td><?php echo $this->player_details['player_last_seen']; ?></td>
      </tr>

      <tr>
        <th>Random Quote</th>
        <td class="<?php if (!$this->random_quote): ?>noquote<?php elseif ($this->random_quote['say_mode'] == 'public'): ?>quote_public<?php else: ?>quote_team<?php endif; ?>">
          <?php if (!$this->random_quote): ?>No quote available<?php else: ?><?php echo replace_color_codes($this->random_quote['say_message']); ?><?php endif; ?>
        </td>
      </tr>

      <tr>
        <th>Favourite Target</th>
        <td><?php if ($this->favorite_target): echo player_link($this->favorite_target['player_id'], $this->favorite_target['player_name']); ?> with <?php echo $this->favorite_target['kill_count']; ?> kills<?php endif; ?></td>
      </tr>
      <tr>
        <th>Nemesis</th>
        <td><?php if ($this->favorite_nemesis): if ($this->favorite_nemesis['player_id'] > 0): echo player_link($this->favorite_nemesis['player_id'], $this->favorite_nemesis['player_name']); else: ?>&lt;world&gt;<?php endif; ?> with <?php echo $this->favorite_nemesis['kill_count']; ?> deaths<?php endif; ?></td>
      </tr>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col class="item" />
      <col />
      <col />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th >Gameplay Stats</th>
        <th >Total</th>
        <th >Alien</th>
        <th >Human</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <th>Time</th>
        <td><?php echo $this->player_details['player_total_time']; ?></td>
        <td><?php echo $this->player_details['player_total_alien']; ?></td>
        <td><?php echo $this->player_details['player_total_human']; ?></td>
        <!-- td><?php echo $this->player_details['player_total_spec']; ?></td -->
      </tr>
      <tr>
        <th>Kills</th>
        <td><?php echo $this->player_details['player_kills']; ?></td>
        <td><?php echo $this->player_details['player_kills_alien']; ?></td>
        <td><?php echo $this->player_details['player_kills_human']; ?></td>
      </tr>
      <tr>
        <th>Assists</th>
        <td><?php echo $this->player_details['player_assists']; ?></td>
        <td><?php echo $this->player_details['player_assists_alien']; ?></td>
        <td><?php echo $this->player_details['player_assists_human']; ?></td>
      </tr>
      <tr>
        <th>Team Kills</th>
        <td><?php echo $this->player_details['player_teamkills']; ?></td>
        <td><?php echo $this->player_details['player_teamkills_alien']; ?></td>
        <td><?php echo $this->player_details['player_teamkills_human']; ?></td>
      </tr>
      <tr>
        <th>Enemy Assists</th>
        <td><?php echo $this->player_details['player_enemyassists']; ?></td>
        <td><?php echo $this->player_details['player_enemyassists_alien']; ?></td>
        <td><?php echo $this->player_details['player_enemyassists_human']; ?></td>
      </tr>
      <tr>
        <th>Deaths (total)</th>
        <td><?php echo $this->player_details['player_deaths']; ?></td>
        <td class="subtotal"><?php echo $this->player_details['player_deaths_enemy_alien'] + $this->player_details['player_deaths_team_alien'] + $this->player_details['player_deaths_world_alien']; ?></td>
        <td class="subtotal"><?php echo $this->player_details['player_deaths_enemy_human'] + $this->player_details['player_deaths_team_human'] + $this->player_details['player_deaths_world_human']; ?></td>
      </tr>
      <tr>
        <th>Deaths by enemy</th>
        <td class="subtotal"><?php echo $this->player_details['player_deaths_enemy_alien'] + $this->player_details['player_deaths_enemy_human']; ?></td>
        <td><?php echo $this->player_details['player_deaths_enemy_alien']; ?></td>
        <td><?php echo $this->player_details['player_deaths_enemy_human']; ?></td>
      </tr>
      <tr>
        <th>Deaths by team</th>
        <td class="subtotal"><?php echo $this->player_details['player_deaths_team_alien'] + $this->player_details['player_deaths_team_human']; ?></td>
        <td><?php echo $this->player_details['player_deaths_team_alien']; ?></td>
        <td><?php echo $this->player_details['player_deaths_team_human']; ?></td>
      </tr>
      <tr>
        <th>Deaths by &lt;world&gt;</th>
        <td class="subtotal"><?php echo $this->player_details['player_deaths_world_alien'] + $this->player_details['player_deaths_world_human']; ?></td>
        <td><?php echo $this->player_details['player_deaths_world_alien']; ?></td>
        <td><?php echo $this->player_details['player_deaths_world_human']; ?></td>
      </tr>
      <tr>
        <th>Skill (<span title="The more games are played against advesaries with skills similar to a player, the less uncertain the computation is." class="tooltip">uncertainty</span>)</th>
        <td><?php skill($this->player_details['skill'], $this->player_details['skill_sigma']) ?></td>
        <td><?php skill($this->player_details['skill_a'], $this->player_details['skill_a_sigma']); ?></td>
        <td><?php skill($this->player_details['skill_h'], $this->player_details['skill_h_sigma']); ?></td>
      </tr>

      <tr>
        <th>Efficiencies</th>
        <td colspan="3"><?php echo $this->player_details['player_total_efficiency'], ' (', $this->player_details['player_kill_efficiency'], ' kill, ', $this->player_details['player_destruction_efficiency']; ?> destruction)</td>
      </tr>
    </tbody>
  </table>

  <div class="split-table">
    <table>
      <colgroup>
        <col />
        <col class="data" />
      </colgroup>

      <thead>
        <tr>
          <th colspan="2">Kills by Weapon</th>
        </tr>
      </thead>

      <tbody>
        <?php if (empty($this->weapon_kills)) { ?>
          <tr class="emptylist"><td colspan="2">No kills</td><tr>
        <?php } else foreach ($this->weapon_kills as $weapon) { ?>
          <tr>
            <td><?php $icon = "images/icons/".(!empty($weapon['weapon_icon']) ? $weapon['weapon_icon'] : "blank.png"); ?><img src="<?php echo $icon; ?>" <?php list($width, $height, $type, $attr) = getimagesize($icon); echo $attr; ?> <?php if ($width == 15): ?>style="margin-right: 15px;"<?php endif; ?> alt="<?php echo $weapon['weapon_name']; ?>" ><?php echo $weapon['weapon_name']; ?></td>
            <td><?php echo $weapon['weapon_count']; ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table><!-- --><table>
      <colgroup>
        <col />
        <col class="data" />
      </colgroup>

      <thead>
        <tr>
          <th colspan="2">Deaths by Weapon</th>
        </tr>
      </thead>

      <tbody>
        <?php if (empty($this->weapon_deaths)) { ?>
          <tr class="emptylist"><td colspan="2">No deaths</td><tr>
        <?php } else foreach ($this->weapon_deaths as $weapon) { ?>
          <tr>
            <td><?php $icon = "images/icons/".(!empty($weapon['weapon_icon']) ? $weapon['weapon_icon'] : "blank.png"); ?><img src="<?php echo $icon; ?>" <?php list($width, $height, $type, $attr) = getimagesize($icon); echo $attr; ?> <?php if ($width == 15): ?>style="margin-right: 15px;"<?php endif; ?> alt="<?php echo $weapon['weapon_name']; ?>" ><?php echo $weapon['weapon_name']; ?></td>
            <td><?php echo $weapon['weapon_count']; ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <div class="split-table">
    <table>
      <colgroup>
        <col />
        <col class="data" />
      </colgroup>

      <thead>
        <tr>
          <th colspan="2">Built Structures</th>
        </tr>
      </thead>

      <tbody>
        <?php if (empty($this->built_structures)) { ?>
          <tr class="emptylist"><td colspan="2">No structures built</td><tr>
        <?php } else foreach ($this->built_structures as $build) { ?>
          <tr>
            <td><?php if (!empty($build['building_icon'])): ?><img src="images/icons/<?php echo $build['building_icon']; ?>" width="15" height="15" alt="<?php echo $build['building_name']; ?>" /> <?php endif; ?><?php echo $build['building_name']; ?></td>
            <td><?php echo $build['building_count']; ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table><!-- --><table>
      <colgroup>
        <col />
        <col class="data" />
      </colgroup>

      <thead>
        <tr>
          <th colspan="2">Destroyed Structures</th>
        </tr>
      </thead>

      <tbody>
        <?php if (empty($this->destroyed_structures)) { ?>
          <tr class="emptylist"><td colspan="2">No structures destroyed</td><tr>
        <?php } else foreach ($this->destroyed_structures as $build) { ?>
          <tr>
            <td><?php if (!empty($build['building_icon'])): ?><img src="images/icons/<?php echo $build['building_icon']; ?>" width="15" height="15" alt="<?php echo $build['building_name']; ?>" /> <?php endif; ?><?php echo $build['building_name']; ?></td>
            <td><?php echo $build['building_count']; ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <div class="split-table">
    <table>
      <colgroup>
        <col />
        <col class="data" />
      </colgroup>

      <thead>
        <tr>
          <th colspan="2">Called Votes</th>
        </tr>
      </thead>

      <tbody>
        <?php if (empty($this->votes_called)) { ?>
          <tr class="emptylist"><td colspan="2">No votes called</td><tr>
        <?php } else foreach ($this->votes_called as $vote) { ?>
          <tr>
             <td><?php echo $vote['vote_type']; ?></td>
             <td><?php echo $vote['vote_count']; ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table><!-- --><table>
      <colgroup>
        <col />
        <col class="data" />
      </colgroup>

      <thead>
        <tr>
          <th colspan="2">Votes Against</th>
        </tr>
      </thead>

      <tbody>
        <?php if (empty($this->votes_against)) { ?>
          <tr class="emptylist"><td colspan="2">No votes against</td><tr>
        <?php } else foreach ($this->votes_against as $vote) { ?>
          <tr>
             <td><?php echo $vote['vote_type']; ?></td>
             <td><?php echo $vote['vote_count']; ?></td>
          </tr>
        <?php } ?>
      <tbody>
    </table>
  </div>

  <table>
    <colgroup>
      <col />
    </colgroup>

    <thead>
      <tr>
        <th>Stats per Game</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td><?php // plot something? cheapest to do the games/kills checks here
          if ($this->player_details['player_games_played'] < 2)
            graph_emptyGraphBox('Nothing of interest… play some more games?');
          elseif (!$this->player_details['player_kills'] && !$this->player_details['player_assists'] && !$this->player_details['player_teamkills'] && !$this->player_details['player_enemyassists'] && !$this->player_details['player_deaths'])
            graph_emptyGraphBox('You\'ve left no corpses yet!');
          else
            graph_killsPerGame($this->player_details['player_id']);
        ?></td>
      </tr>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col />
      <col />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th colspan="3">Aliases</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <?php if( constant('PRIVACY_NAME') == '1' ): ?>
          <td colspan="3"><div class="privacy">( Aliases not shown )</div></td>
        <?php else: ?>
          <?php $column = 0; ?>
          <?php foreach($this->player_nicks as $other_nick): ?>
            <td class="playername">
              <?php echo replace_color_codes($other_nick['nick_name']); ?>
            </td>
            <?php if ( $column == 2): ?>
              </tr><tr>
            <?php $column = 0; else: $column++; endif; ?>
          <?php endforeach; ?>
          <?php while( $column < 3 ): $column++; echo "<td></td>"; endwhile; ?>
        <?php endif; ?>
      </tr>
    </tbody>
  </table>
<?php } /* not a bot */ ?>

</div>

<?php include '__footer__.tpl.php'; ?>
