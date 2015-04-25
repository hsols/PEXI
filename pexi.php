<?php
// PEXI PermissionsEX Index for PHP
// Version: 1.0
// Author:  Nuts.io @Zivci
// License: GNU-GPL 3.0

// MySQL Authentication
$servername = ""; // MySQL server (Default: localhost)
$username = ""; // MySQL User
$password = ""; // MySQL Password
$dbname = ""; // MySQL Database

// ------- Do not edit anything below this line ------- //
function array_natsort_list($array) {
    for ($i=func_num_args();$i>1;$i--) {
        $sort_by = func_get_arg($i-1);
        $new_array = array();
        $temporary_array = array();
        foreach($array as $original_key => $original_value) {
            $temporary_array[] = $original_value[$sort_by];
        }
        natsort($temporary_array);
        $temporary_array = array_unique($temporary_array);
        foreach($temporary_array as $temporary_value) {
            foreach($array as $original_key => $original_value) {
                if($temporary_value == $original_value[$sort_by]) {
                    $new_array[$original_key] = $original_value;
                }
            }
        }
        $array = $new_array;
    }
    return $array;
}
// Extract Permission Nodes
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$perms = array();
$sql = "SELECT id, name, permission, world FROM permissions WHERE type = '0' ORDER BY permission ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $perms[] = array(
          "id" => $row["id"],
          "name" => $row["name"],
          "permission" => $row["permission"],
          "world" => $row["world"]
        );
    }
} else {
    echo "<i>No private nodes associated</i><br />";
}
$conn->close();
// Extract Groups
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$groups = array();
$sql = "SELECT id, name FROM permissions_entity WHERE type = '0' ORDER BY id ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $groups[] = array(
          "id" => $row["id"],
          "name" => $row["name"],
          "rank" => ""
        );
    }
} else {
    $groups[] = array(0, "No groups detected.", 0);
}
$conn->close();
// Extract Group Ranks
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$ranks = array();
$sql = "SELECT name, value FROM permissions WHERE type = '0' ORDER BY value DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $ranks[] = array(
          "name" => $row["name"],
          "rank" => $row["value"]
        );
    }
} else {
    $ranks[] = array("0", "No groups detected.");
}
$conn->close();
// Add rank field to each group, if found
foreach($groups as $key => $group){
  foreach($ranks as $rank){
    if(is_numeric($rank['rank'])){
      if($rank['name'] == $group['name']) {
        $groups[$key]['rank'] = $rank['rank'];
      }
    }
  }
}
// Sort groups array by rank field, DESC
foreach($groups as $group) {
     $sort[] = $group['rank'];
}
array_multisort($sort, SORT_DESC, $groups);
// Natural-sort node array, ASC
array_natsort_list($perms);
?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Nuts.io @Zivci">
    <title>PEX Permissions</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">
  </head>
  <body>
    <div class="container">
      <div class="page-header">
        <h1>PEX Permissions</h1>
        <p class="lead">Real-time permission subsystem index with groups and nodes.</p>
      </div>
<?php
        foreach($groups as $group){
          echo '      <div class="row" style="margin-bottom:40px;">'."\n";
          echo '        <div class="col-md-12">'."\n".'          <h3>' . $group['name'];
          if($group['rank'] != "") {
            echo ' <small class="text-muted">#' . $group['rank'] . '</small>';
          }
          echo '</h3>'."\n".'        </div>'."\n";
          foreach ($perms as $perm)
          {
            if ($perm['name'] == $group['name'])
            {
              if($perm['permission'] != "rank" && $perm['permission'] != "prefix" && $perm['permission'] != "default" && $perm['permission'] != "suffix") {
                echo '        <div class="col-md-4 col-sm-6 col-xs-12">'."\n";                
                echo '          ' . $perm['permission'];
                if($perm['world'] != null) {
                  echo ' <small><span class="text-muted" style="font-weight:bold;">'. $perm['world'] .'</span></small>';
                } else {
                  echo ' <small><span class="text-success" style="font-weight:bold;">Global</span></small>';                
                }
                echo "\n".'        </div>'."\n";
              }
            }
          } 
          echo '      </div>';
        }
?>
    </div>
    <footer class="footer">
      <div class="container">
        <p class="text-muted">PEXI PermissionsEX Index</p>
      </div>
    </footer>
    <script src="//code.jquery.com/jquery-2.1.3.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
  </body>
</html>
