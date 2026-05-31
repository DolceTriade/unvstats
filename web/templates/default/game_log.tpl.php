<?php include '__header__.tpl.php'; ?>

<section>
  <header>
    <h2>Game #<?php echo $this->game_details['game_id']; ?> Log</h2>
    <div class="headinglink"> ( <a href="game_details.php?game_id=<?php echo $this->game_details['game_id'] ?>">show game summary</a> | <a href="<?php echo htmlspecialchars($this->include_bots_toggle_url, ENT_QUOTES); ?>"><?php echo $this->include_bots ? 'hide bots' : 'include bots'; ?></a> )</div>
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

  <?php if( constant('PRIVACY_CHAT') == '1' && constant('PRIVACY_LOGS') != '1' ): ?>
    <div class="privacy">( Player chat messages are not shown )</div>
  <?php endif; ?>

  <table>
    <colgroup>
      <col class="data" />
      <col class="datadouble" />
      <col class="weaponicon" />
      <col class="data" />
      <col />
      <col class="data" />
    </colgroup>

    <thead>
      <tr>
        <th>Time</th>
        <th>Player</th>
        <th colspan="2">Action</th>
        <th>Target</th>
        <th>Assistant</th>
      </tr>
    </tbody>

    <tbody>
      <?php if( constant('PRIVACY_LOGS') == '1' ): ?>
        <tr>
          <td colspan="5">Game logs are disabled for privacy</td>
        </tr>
      <?php elseif (isset($this->logs) and count($this->logs)): ?>

      <?php foreach ($this->logs AS $log): ?>
       <tr class="list" >
        <?php if (isset($log['say_gametime'])): ?>
         <td><?php echo $log['say_gametime'] ?></td>
         <td class="playername"><?php if ($log['player_id'] == 0) echo 'console'; else { echo player_link($log['player_id'], $log['player_name']); } ?></td>
         <td></td>
         <td></td>
         <td colspan="2"><span class="quakecolor_<?php if ($log['say_mode'] == 'alien'): echo 'red'; elseif ($log['say_mode'] == 'human'): echo 'cyan'; elseif ($log['say_mode'] == 'spectator'): echo 'yellow'; else: echo 'green'; endif; ?>"><?php echo htmlspecialchars($log['say_message'],ENT_QUOTES); ?></span></td>

        <?php elseif (isset($log['kill_gametime'])): ?>
         <td><?php echo $log['kill_gametime'] ?></td>
         <td class="playername"><?php if (!empty($log['killer_id'])) echo player_link($log['killer_id'], $log['killer_name']); ?></td>
         <td><?php if (!empty($log['weapon_icon'])): ?><img src="images/icons/<?php echo $log['weapon_icon']; ?>" alt="<?php echo $log['weapon_name']; ?>" <?php list($width, $height, $type, $attr) = getimagesize('images/icons/'.$log['weapon_icon']); echo $attr; ?>><?php endif; ?></td>
         <td><?php if ($log['kill_type'] == 'team'): ?><span class="quakecolor_red">teamkilled</span><?php elseif ($log['kill_type'] == 'enemy'): ?>killed<?php elseif (!empty($log['weapon_icon'])): ?>killed<?php endif; ?></td>
         <td<?php if (empty($log['assistant_id'])) echo ' colspan="2"'?>><?php if (!empty($log['victim_id'])) echo player_link($log['victim_id'], $log['victim_name']); if (empty($log['weapon_icon'])): ?> died by <?php echo $log['weapon_name']; endif;?></td>
         <?php if (!empty($log['assistant_id'])) { ?><td><?php if ($log['kill_assist_type'] == 'team') { ?><span class="quakecolor_red">⚠</span> <?php } echo player_link($log['assistant_id'], $log['assistant_name']);?></td><?php } ?>

        <?php elseif (isset($log['destruct_gametime'])): ?>
         <td><?php echo $log['destruct_gametime'] ?></td>
         <td class="playername"><?php if (!empty($log['player_id'])) echo player_link($log['player_id'], $log['player_name']); ?></td>
         <td><?php if (!empty($log['weapon_icon'])): ?><img src="images/icons/<?php echo $log['weapon_icon']; ?>" alt="<?php echo $log['weapon_name']; ?>" <?php list($width, $height, $type, $attr) = getimagesize('images/icons/'.$log['weapon_icon']); echo $attr; ?>><?php endif; ?></td>
         <td><?php if ($log['weapon_team'] == $log['building_team'] ): ?><span class="quakecolor_yellow">destroyed</span> <?php else: ?>destroyed <?php endif; ?></td>
         <td colspan="2"><img src="images/icons/<?php echo $log['building_icon']; ?>" <?php list($width, $height, $type, $attr) = getimagesize('images/icons/'.$log['building_icon']); echo $attr; ?>> <?php echo $log['building_name'] ?>
             <?php if( empty($log['weapon_icon'] )): ?> by <?php echo $log['weapon_name']; endif; ?>
         </td>

        <?php elseif (isset($log['build_gametime'])): ?>
         <td><?php echo $log['build_gametime'] ?></td>
         <td class="playername"><?php if (!empty($log['player_id'])) echo player_link($log['player_id'], $log['player_name']); ?></td>
         <td></td>
         <td><span class="quakecolor_green">built</span></td>
         <td colspan="2"><img src="images/icons/<?php echo $log['building_icon']; ?>" <?php list($width, $height, $type, $attr) = getimagesize('images/icons/'.$log['building_icon']); echo $attr; ?>> <?php echo $log['building_name'] ?></td>

        <?php elseif (isset($log['decon_gametime'])): ?>
         <td><?php echo $log['decon_gametime'] ?></td>
         <td class="playername"><?php if (!empty($log['player_id'])) echo player_link($log['player_id'], $log['player_name']); ?></td>
         <td></td>
         <td><span class="quakecolor_yellow">deconned</a></td>
         <td colspan="2"><img src="images/icons/<?php echo $log['building_icon']; ?>" <?php list($width, $height, $type, $attr) = getimagesize('images/icons/'.$log['building_icon']); echo $attr; ?>> <?php echo $log['building_name'] ?></td>

        <?php elseif (isset($log['vote_gametime'])): ?>
         <td><?php echo $log['vote_gametime'] ?></td>
         <td class="playername"><?php if (!empty($log['caller_id'])) echo player_link($log['caller_id'], $log['caller_name']); ?></td>
         <td></td>
         <td><span class="quakecolor_magenta"><?php if ($log['vote_mode'] == 'public') echo "vote"; else echo "teamvote"; ?></span></td>
         <td colspan="2">
          <?php echo $log['vote_type']; ?>
          <?php if (!empty($log['victim_id'])) echo player_link($log['victim_id'], $log['victim_name']); ?>
          <?php if (!empty($log['vote_arg'])) echo $log['vote_arg']; ?>
        </td>

        <?php elseif (isset($log['endvote_gametime'])): ?>
         <td><?php echo $log['endvote_gametime'] ?></td>
         <td></td>
         <td></td>
         <td><span class="quakecolor_white"><?php if ($log['endvote_mode'] == 'public'): echo "vote"; else: echo "teamvote"; endif; ?></span></td>
         <td colspan="2">
          <?php if( $log['endvote_pass'] == 'yes' ): echo "passed"; else: echo "failed"; endif; echo " ( " . $log['endvote_yes'] . " - " . $log['endvote_no'] . " )" ?>
        </td>

        <?php elseif (isset($log['game_length'])): ?>
         <td><?php echo $log['game_length'] ?></td>
         <td></td>
         <td></td>
         <td><span class="quakecolor_cyan">map end</span></td>
         <td colspan="2"><b><?php echo $log['game_winner'] ?> <?php if ($log['game_winner'] == 'aliens' || $log['game_winner'] == 'humans'): ?> win<?php endif;?></b></td>

        <?php elseif (isset($log['misc_gametime'])): ?>
         <td><?php echo $log['misc_gametime'] ?></td>
         <td></td>
         <td></td>
         <td><span class="quakecolor_cyan"><?php echo $log['misc_action'] ?></span></td>
         <td colspan="2"><?php echo $log['misc_text'] ?></td>
        <?php endif; ?>

       </tr>
      <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5">No action occured</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

 </section>

 <?php include '__footer__.tpl.php'; ?>
