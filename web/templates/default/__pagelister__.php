<?php
$htmlhandler = new PageLister_HtmlHandler();

$this->pagelister->SetHtmlHandler(new PageLister_HtmlHandler());

function player_link($id, $name)
{
  global $bots; // ugh
  return in_array($id, $bots)
         ? '<span class="bot">'.replace_color_codes($name).'</span>'
         : ('<a href="player_details.php?player_id='.$id.'">'.replace_color_codes($name).'</a>');
}
?>
