<?php

require_once __DIR__.'/functions.php';
require_once __DIR__.'/backUpDB.php';


// Clear logs > 14 days
clearOldlogs($dtb);

// Clear users with last conn > 3 months
clearOldUsers($dtb);

// Create a backup of db store it in /db_dumps/, delete files older than 7 days
genDBBackup();

echo "<br /> Cron job done.";

