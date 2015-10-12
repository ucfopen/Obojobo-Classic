<?php
require_once("internal/app.php");
require("login.php"); // saml will redirect inside login.php
header("Location: /"); // saml didn't do anything, redirect here

