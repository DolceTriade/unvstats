<?php include '__header__.tpl.php'; ?>

<?php $columns=5;
      if(isset($this->order_name)):
        $columns+=1;
      endif;

      $game_search="";
      if(isset($this->map_id)):
        $game_search="&amp;map_id=".$this->map_id;
      endif;
      if(isset($this->hideempty)):
        $game_search.="&amp;hideempty=1";
      endif;

      $game_empty="";
      if(isset($this->order_name)):
        $game_empty="&amp;order=".$this->order_name;
      endif;
      if(isset($this->map_id)):
        $game_empty.="&amp;map_id=".$this->map_id;
      endif;
?>

<section>
  <header>
    <h2><?php if(isset($this->order_name)): echo "Games with most ".$this->order_name; else: echo "Recent Games"; endif; ?></h2>
    <div class="headinglink">
     Sort by: <a href="games.php?order=gameid<?php echo $game_search ?>">Game ID</a> | <a href="games.php?order=kills<?php echo $game_search; ?>">Kills</a> | <a href="games.php?order=deaths<?php echo $game_search; ?>">Deaths</a> | <a href="games.php?order=length<?php echo $game_search; ?>">Length</a>
     ♢
     Show empty games: <a href="games.php?hideempty=<?php echo (isset($this->hideempty)) ? "0" : "1"; echo $game_empty; ?>"><?php echo (isset($this->hideempty)) ? "No" : "Yes"; ?></a>
     ♢
     Bots: <a href="<?php echo htmlspecialchars($this->include_bots_toggle_url, ENT_QUOTES); ?>"><?php echo $this->include_bots ? 'Included' : 'Excluded'; ?></a>
    </div>
  </header>

  <table>
    <colgroup>
      <col class="data" />
      <col class="datadouble" />
      <col />
      <col class="datamore" />
      <col class="datamore" />
      <?php if(isset($this->order_name)): ?>
        <col class="datamore" />
      <?php endif; ?>
    </colgroup>

    <thead>
      <tr>
        <th>Game ID</th>
        <th>Date <small>(UTC)</small></th>
        <th>Map</th>
        <th>Length</th>
        <th>Winner</th>
        <?php if(isset($this->order_name)): ?>
          <th><?php echo $this->order_name; ?></th>
        <?php endif; ?>
      </tr>
    </thead>

    <tfoot>
      <tr>
        <td colspan="<?php echo $columns; ?>">
          Pages: <?php echo $this->pagelister->GetHTML(); ?>
        </td>
      </tr>
    </tfoot>

    <tbody>
      <?php foreach ($this->games AS $game): ?>
        <tr class="list" >
          <td><a href="game_details.php?game_id=<?php echo $game['game_id']; ?>"><?php echo $game['game_id']; ?></a></td>
          <td><?php echo $game['game_timestamp']; ?></td>
          <td><?php echo replace_color_codes($game['game_map_name']); ?></td>
          <td align="right"><?php echo $game['game_length']; ?></td>
          <td align="center"><?php echo $game['game_winner']; ?></td>
          <?php if(isset($this->order_name)): ?>
            <td><?php echo $game['stats_count']; ?></td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>

      <?php if (!count($this->games)): ?>
        <tr>
          <td colspan="<?php echo $columns; ?>">No games yet</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

 </section>

 <?php include '__footer__.tpl.php'; ?>
