<?php include '__header__.tpl.php'; ?>

<section>
  <header>
    <h2>Map Details for <?php echo replace_color_codes($this->map_details['map_text_name']); ?></h2>
    <div class="headinglink"><a href="games.php?map_id=<?php echo $this->map_details['map_id'] ?>">See game list for this map</a> | <a href="<?php echo htmlspecialchars($this->include_bots_toggle_url, ENT_QUOTES); ?>"><?php echo $this->include_bots ? 'hide bots' : 'include bots'; ?></a></div>
  </header>

  <div class="split-table">
    <div>
      <table>
        <colgroup>
          <col class="item" />
          <col />
        </colgroup>

        <thead>
          <tr>
            <th colspan="3">Summary</th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td colspan="2" class="levelshot"><img class="levelshot" alt="<?php echo htmlspecialchars($this->map_details['map_text_name'],ENT_QUOTES); ?>" src="_levelshot.php?map_id=<?php echo ($this->map_details['map_id']); ?>" /></td>
          </tr>
          <tr>
            <td>Short Name</td>
            <td><?php echo $this->map_details['map_name']; ?></td>
          </tr>
          <tr>
            <td>Games</td>
            <td><?php echo $this->map_details['mapstat_games']; ?></td>
          </tr>
          <tr>
            <td>Alien Wins</td>
            <td><?php echo $this->map_details['mapstat_alien_wins']; ?></td>
          </tr>
          <tr>
            <td>Human Wins</td>
            <td><?php echo $this->map_details['mapstat_human_wins']; ?></td>
          </tr>
          <tr>
            <td>Ties</td>
            <td><?php echo $this->map_details['mapstat_ties']; ?></td>
          </tr>
          <tr>
            <td>Draws</td>
            <td><?php echo $this->map_details['mapstat_draws']; ?></td>
          </tr>
          <tr>
            <td>Total Time</td>
            <td><?php echo $this->map_details['mapstat_text_time']; ?></td>
          </tr>
        </tbody>
      </table>

      <table>
        <colgroup>
          <col class="item" />
          <col class="data" />
          <col class="data" />
          <col />
        </colgroup>

        <thead>
          <tr>
            <th>Team Breakdown</th>
            <th>Wins</th>
            <th>Kills</th>
            <th>Deaths</th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td>Aliens</td>
            <td><?php echo $this->map_details['mapstat_alien_wins']; ?></td>
            <td><?php echo $this->map_details['mapstat_alien_kills']; ?></td>
            <td><?php echo $this->map_details['mapstat_alien_deaths']; ?></td>
          </tr>
          <tr>
            <td>Humans</td>
            <td><?php echo $this->map_details['mapstat_human_wins']; ?></td>
            <td><?php echo $this->map_details['mapstat_human_kills']; ?></td>
            <td><?php echo $this->map_details['mapstat_human_deaths']; ?></td>
          </tr>
        </tbody>
      </table>

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
            <tr class="emptylist"><td colspan="3">No votes called</td></tr>
          <?php } else foreach ($this->votes_called as $vote) { ?>
          <tr>
            <td><?php echo $vote['vote_type']; ?></td>
            <td><?php echo $vote['vote_count']; ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div><!--

 --><table>
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
          <tr class="emptylist"><td colspan="3">No kills</td></tr>
        <?php } else foreach ($this->weapon_kills as $weapon) { ?>
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
          <tr class="emptylist"><td colspan="3">No structures built</td></tr>
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
          <tr class="emptylist"><td colspan="3">No structures destroyed</td></tr>
        <?php } else foreach ($this->destroyed_structures as $build) { ?>
        <tr>
          <td><?php if (!empty($build['building_icon'])): ?><img src="images/icons/<?php echo $build['building_icon']; ?>" width="15" height="15" alt="<?php echo $build['building_name']; ?>" /> <?php endif; ?><?php echo $build['building_name']; ?></td>
          <td><?php echo $build['building_count']; ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

</section>

<?php include '__footer__.tpl.php'; ?>
