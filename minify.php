<?php
require_once("internal/app.php");
$min_customConfigPaths = ['base' => \AppCfg::DIR_BASE.'internal/config/minify.php'];
require(\AppCfg::DIR_BASE.'internal/vendor/mrclay/minify/min/index.php');
