<?php
#[AllowDynamicProperties]
class TremulousReporter {
  function __construct ($address) {
    list($ip, $port) = explode(':', $address);

    $this->ip          = $ip;
    $this->port        = (int)$port;
    // $this->checkstring = md5('iD-Software-'.$server.'-'.$port);
  }

  public function getStatus () {
    if (!$this->connect()) return false;

    $this->sendQuery('getstatus');
    $data_string = fread($this->fp, 10000);
    $data_string = substr($data_string, 19);

    $data = explode("\n", $data_string);


    $server_vars = array();
    $split = explode('\\', $data[0]);
    for ($i=1; $i<count($split); $i+=2) {
      if ($split[$i] == 'P') $split[$i+1] = str_replace('-', '', $split[$i+1]);
      $server_vars[$split[$i]] = $split[$i+1];
    }
    //die(print_r($server_vars));
    $humans = array();
    $aliens = array();
    $specs  = array();
    foreach ($data as $key => $player) {
      if ($key === 0) continue;

      if (preg_match("#^([0-9-]+) (\d+) \"(.*)\"$#", $player, $result)) {
        $pinfo = array(
          "kills" => $result[1],
          "ping"  => $result[2],
          "name"  => $result[3]
        );

        if ($server_vars['P'][$key-1] == 2)
          $humans[] = $pinfo;
        elseif ($server_vars['P'][$key-1] == 1)
          $aliens[] = $pinfo;
        else
          $specs[] = $pinfo;
      }
    }

    $this->disconnect();

    return array('humans'      => $humans,
                 'aliens'      => $aliens,
                 'specs'       => $specs,
                 'server_vars' => $server_vars);
  }

  private function connect () {
    $this->fp = @fsockopen('udp://'.$this->ip, $this->port, $errno, $errstr, 2);

    if (!$this->fp)
      return false;
    else {
      socket_set_timeout($this->fp, 2);
      return true;
    }
  }

  private function disconnect () {
    fclose($this->fp);
  }

  private function sendQuery ($command) {
    fwrite($this->fp, "\xff\xff\xff\xff".$command);
  }
}
?>
