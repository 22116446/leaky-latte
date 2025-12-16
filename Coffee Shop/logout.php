<?php
require __DIR__ . '/db.php';
require 'auth.php';
session_destroy();
header('Location: index.html');
exit;
