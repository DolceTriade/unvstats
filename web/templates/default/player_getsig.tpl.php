<?php include '__header__.tpl.php'; ?>

<?php
 $styles = array (
   array ( 'id' => '0', 'label' => 'Compact Card', 'width' => 400, 'height' => 46 ),
   array ( 'id' => '1', 'label' => 'Split Panel', 'width' => 500, 'height' => 40 ),
   array ( 'id' => '2', 'label' => 'Quote Banner', 'width' => 500, 'height' => 40 ),
 );
?>

<section>
  <header>
    <h2>Player Signature for <?php echo player_link($this->player_details['player_id'], $this->player_details['player_name']); ?></h2>
  </header>

  <table>
    <colgroup>
      <col class="item" />
      <col />
    </colgroup>

    <?php foreach ($styles AS $style):
      $path      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
      $base      = "http://" . $_SERVER['HTTP_HOST'] . substr($path, 0, strrpos($path, "/") + 1 );
      $playerurl = $base . "player_details.php?player_id=" . $this->player_details['player_id'];
      $sigurl    = $base . "player_sig.php?player_id=" . $this->player_details['player_id'] . "&amp;style=" . $style['id'];
      $imgurl    = "&lt;img src=&quot;" . $sigurl . "&quot; width=&quot;" . $style['width'] . "&quot; height=&quot;" . $style['height'] . "&quot;&gt;";
      $linkurl   = "&lt;a href=&quot;" . $playerurl ."&quot;&gt;" . $imgurl . "&lt;/a&gt;";
      $bbcode    = "[url=" . $playerurl . "][img]" . $sigurl . "[/img][/url]";
    ?>

    <thead>
      <tr>
        <th colspan="2">Style <?php echo $style['id']; ?>: <?php echo htmlspecialchars($style['label'], ENT_QUOTES); ?></th>
      </tr>
    </thead>

    <tbody class="padded" >
      <tr>
        <td>Sample</td>
        <td><?php echo "<img src=\"player_sig.php?player_id=" . $this->player_details['player_id'] . "&amp;style=" . $style['id'] . "\" width=\"" . $style['width'] . "\" height=\"" . $style['height'] . "\" >"; ?></td>
      </tr>
      <tr>
        <td>URL to image</td>
        <td><input class="sig" type="text" readonly="readonly" value="<?php echo $sigurl; ?>" style="width:100%" ></td>
      </tr>
      <tr>
        <td>HTML for image</td>
        <td><input class="sig" type="text" readonly="readonly" value="<?php echo $imgurl; ?>" style="width:100%" ></td>
      </tr>
      <tr>
        <td>HTML with link</td>
        <td><input class="sig" type="text" readonly="readonly" value="<?php echo $linkurl; ?>" style="width:100%" ></td>
      </tr>
      <tr>
        <td>BBCODE</td>
        <td><input class="sig" type="text" readonly="readonly" value="<?php echo $bbcode; ?>" style="width:100%" ></td>
      </tr>
    </tbody>

    <?php endforeach; ?>
  </table>

</section>

<?php include '__footer__.tpl.php'; ?>
