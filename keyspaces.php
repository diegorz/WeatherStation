<html>
    <head>
        <meta charset="utf-8"/>
        <title>Retrieve keyspaces</title>
    </head>
    <body>

<?php
$cluster = Cassandra::cluster()
        ->withContactPoints('10.200.117.244')
        ->build();

$session = $cluster->connect();
echo sprintf("<p>Connected to cluster %s</p>", '10.200.117.244');

$keyspaces = $session->schema()->keyspaces();

echo "<table border=\"1\">";
echo "<tr><th>Keyspace</th><th>Table</th></tr>";
foreach ($keyspaces as $keyspace) {
    foreach ($keyspace->tables() as $table) {
        echo sprintf("<tr><td>%s</td><td>%s</td></tr>\n", $keyspace->name(), $table->name());
    }
}
echo "</table>";
?>

    </body>
</html>

