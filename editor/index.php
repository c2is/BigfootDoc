<?php
session_start();
if (! isset($_SESSION["language"])) {
    $_SESSION["language"] = "en";
}

$ajaxUrl = "./editor.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Skriv Markup Language</title>
    <link href="css/bootstrap-2.2.2.min.css" rel="stylesheet" media="screen" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <link href="css/style.css" rel="stylesheet" media="screen" />
    <style type="text/css">
        html, body {
            background-color: #eaeaea;
            margin: 0;
            border: 0;
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            height: 100%;
        }
        div#body-content {
            position: absolute;
            top: 50px;
            bottom: 5px;
            left: 0;
            right: 0;
        }
            /* textarea */
        textarea#skrivtext {
            position: absolute;
            font-family: monospace;
            margin: 1%;
            width: 48%;
            left: 0;
            top: 0;
            bottom: 0;
            color: #000;
        }
            /* content */
        div#skrivhtml {
            position: absolute;
            overflow: auto;
            margin: 1%;
            width: 46%;
            right: 0;
            top: 0;
            bottom: 0;
            border: 1px solid #000;
            padding: 1%;
            background-color: #fff;
        }
        div#skrivhtml h1 {
            margin-top: 1em;
        }
            /* table */
        div#skrivhtml table.bordered {
            border: 1px solid #888;
        }
        div#skrivhtml table.bordered th {
            border: 1px solid #888;
            padding: 3px;
            background-color: #eee;
        }
        div#skrivhtml table.bordered td {
            border: 1px solid #888;
            padding: 3px;
        }
            /* footnotes */
        div#skrivhtml div.footnotes {
            margin-top: 2em;
            border-top: 1px dashed #aaa;
            padding-top: 1em;
        }
        div#skrivhtml div.footnotes p.footnote {
            margin: 0;
            font-size: 0.9em;
        }
        div#ajaxMsg {
            display:block;
            float: none;
            padding: 10px 15px 10px;
            color: #AD2929;
            text-decoration: none;
        }
        li#lang div{
            display:inline;
            float: none;
            padding: 10px 15px 10px;
            text-decoration: none;
            cursor:pointer;
        }
    </style>
</head>
<body >
<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container-fluid">
            <a class="brand" href="/">BigFoot Documentation</a>
            <ul class="nav">
                <li id="lang"><div id="en">EN</div>|<div id="fr">FR</div></li>
                <li><a href="#_" onclick="build();">Build Doc in Html</a></li>
                <li><div id="ajaxMsg"></div></li>
            </ul>

        </div>
    </div>
</div>
<div id="body-content">
    <textarea id="skrivtext"><?php echo file_get_contents("../".$_SESSION["language"]."/doc.skriv");?></textarea>
    <div id="skrivhtml">
    </div>
</div>

<script type="text/javascript"><!--
    function build(){
        $("#ajaxMsg").load('<?php echo $ajaxUrl;?>', {action: 'build'});
    }
    $(window).bind(
            "beforeunload",
            function() {
                $("#ajaxMsg").load('<?php echo $ajaxUrl;?>', {action: 'shutdown'});
            }
    )
    $(document).ready(function() {
        $("#<?php echo $_SESSION["language"]?>").css("color", "red");
        $("#<?php echo $_SESSION["language"]?>").css("cursor", "auto");

        var text = $("#skrivtext").val();
        $("#skrivhtml").load('<?php echo $ajaxUrl;?>', {action: 'convert',text: text});
    });

    $("#en").click(
            function() {
                $("#ajaxMsg").load('<?php echo $ajaxUrl;?>', {action: 'setlg', language: "en"});
                window.location.reload();
            }
    )
    $("#fr").click(
            function() {
                $("#ajaxMsg").load('<?php echo $ajaxUrl;?>', {action: 'setlg', language: "fr"});
                window.location.reload();
            }
    )


    var timer = null;
    // met le focus sur la zone de texte
    $("#skrivtext").focus();
    // événement sur la modification de la zone de texte
    $("#skrivtext").bind("input propertychange", function() {
        if (timer)
            clearTimeout(timer);
        timer = setTimeout(function() {
            var text = $("#skrivtext").val();
            $("#skrivhtml").load('<?php echo $ajaxUrl;?>', {action: 'convert',text: text});
            $("#ajaxMsg").load('<?php echo $ajaxUrl;?>', {action: 'save',text: text});
        }, 300);
    });
//--></script>




</body>
</html>