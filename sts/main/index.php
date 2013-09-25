<?php
//
// index.php: main stump for all interactive pages for this project (whichever)
// Project: STS - Specialised Test Setter
//

//echo "hello";

include "Autoload.php";       // get class files from suitable directories

Globals::set("jquery", "../non-source");
Globals::set("scripts", "../scripts");
Globals::set("style", "../style");

session_start();              // enable basic cookies

Session::forget("baseUrl");
//echo "baseUrl = " . Session::get("baseUrl"). "<br/>";
Url::checkBase();             // set up, so we know "this" URL

Dispatch::go();               // go do it ...
