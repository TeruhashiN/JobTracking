<?php
session_start();
session_destroy();
header("Location: ../function/dashboard.php?logout=success");
exit;
