<?php

include("functions.php");

// Clear logs > 14 days
clearOldlogs($dtb);

// Clear users with last conn > 3 months
clearOldUsers($dtb);

// Send Mail as backup
// sendBackupMail();
